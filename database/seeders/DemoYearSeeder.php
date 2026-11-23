<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\BusinessProduct;
use App\Models\Category;
use App\Models\City;
use App\Models\Hr\Announcement;
use App\Models\Hr\Asset;
use App\Models\Hr\AttendanceRegularization;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use App\Models\Hr\ExpenseClaim;
use App\Models\Hr\Holiday;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\Shift;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Models\WithdrawalRequest;
use App\Services\BranchManagerService;
use App\Services\BranchPermissionMatrixService;
use App\Services\BusinessSalesService;
use App\Services\CommissionPartnerService;
use App\Services\Hr\AssetService;
use App\Services\Hr\AttendanceService;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\ExpenseService;
use App\Services\Hr\LeaveService;
use App\Services\Hr\PayrollService;
use App\Services\Hr\WorkCalendarService;
use App\Services\OrderService;
use App\Services\PartnerPayoutService;
use App\Services\PermissionMatrixService;
use App\Services\SubAdminService;
use App\Services\VipMemberService;
use App\Services\VipRenewalService;
use App\Services\WithdrawalService;
use App\Support\BusinessModules;
use Closure;
use Database\Seeders\DemoYear\DemoYearData as D;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use SplPriorityQueue;
use Throwable;

/**
 * One full year of believable activity for reports and demos.
 *
 * Creates Admins, Sub Admins, Branch Managers, Commission Partners, 19 VIP
 * businesses (with real photos, services, reviews, own products), ~70
 * shoppers and their orders, plus a 14-person HR team with daily attendance,
 * leave, expenses, assets, salary advances and 11 finalized payroll runs.
 *
 * The year is replayed as a timeline: the clock is moved to each moment
 * (Carbon::setTestNow) and the app's own services do the work — activation
 * splits, product commission, wallet credits on delivery, renewals, partner
 * payouts, withdrawals, business settlements, leave balances and payroll —
 * so every report shows numbers the app itself produced.
 *
 * Photos: storage/app/public/uploads/demo/year (Pexels, see
 * database/demo-year-photo-credits.txt). Emails are never sent: the mailer is
 * switched to `array` while seeding.
 *
 * Re-runnable: it first removes everything it created before (users on the
 * demo domain or in its staff list, their orders, payroll runs, assets…).
 *
 *   php artisan db:seed --class=DemoYearSeeder
 *   Every login: <first>.<last>@globallife.co.in / Password!123
 *   VIP logins:  <business-key>@demo.globallife.co.in / Password!123
 */
class DemoYearSeeder extends Seeder
{
    private const PHOTOS = 'uploads/demo/year';

    private Carbon $start;

    private Carbon $end;

    private SplPriorityQueue $queue;

    private int $seq = 0;

    private User $superAdmin;

    /** @var array<string, User> person key => user */
    private array $users = [];

    /** @var array<string, VipMicrosite> business key => microsite */
    private array $sites = [];

    /** @var array<string, array<string, mixed>> business key => data */
    private array $biz = [];

    /** @var array<string, Employee> */
    private array $employees = [];

    /** @var array<string, VipPlan> */
    private array $plans = [];

    /** @var array<string, City> */
    private array $cities = [];

    /** @var Collection<int, Product> */
    private Collection $catalogue;

    /** @var array<string, list<array<string, mixed>>> city slug => shopper pool */
    private array $shoppers = [];

    /** @var array<string, array<string, true>> employee key => planned leave dates (Y-m-d) */
    private array $leaveDays = [];

    /** @var array<string, array<string, true>> employee key => days to be regularized */
    private array $regularizeDays = [];

    private array $counts = ['orders' => 0, 'leads' => 0, 'attendance' => 0, 'skipped' => 0];

    public function run(): void
    {
        mt_srand(20251124);

        // Never email anyone while replaying a year.
        config(['mail.default' => 'array', 'queue.default' => 'sync']);

        $this->end = Carbon::now();
        $this->start = $this->end->copy()->subYear()->addDay()->startOfDay();

        $this->superAdmin = User::role('super_admin')->orderBy('id')->first()
            ?? throw new \RuntimeException('DemoYearSeeder needs a Super Admin. Run the main DatabaseSeeder first.');

        foreach (['growth', 'professional', 'growth-plus', 'premium'] as $slug) {
            $this->plans[$slug] = VipPlan::where('slug', $slug)->first()
                ?? throw new \RuntimeException("DemoYearSeeder needs the '{$slug}' VIP plan. Run VipPlanSeeder first.");
        }

        foreach (['lucknow', 'jhansi', 'pune', 'mumbai', 'hyderabad', 'bengaluru'] as $slug) {
            $this->cities[$slug] = City::where('slug', $slug)->first()
                ?? throw new \RuntimeException("DemoYearSeeder needs the '{$slug}' city. Run CitySeeder first.");
        }

        Role::findOrCreate('customer', 'web');

        $this->command?->info('Removing previous demo-year data…');
        $this->cleanup();

        $this->queue = new SplPriorityQueue;

        $this->setTime($this->start->copy()->setTime(9, 30));
        $this->prepareCatalogue();
        $this->prepareHr();

        $this->planNetwork();
        $this->planEmployees();
        $this->planCommerce();
        $this->planLeads();
        $this->planMoneyCycles();

        $this->command?->info('Replaying '.$this->start->toDateString().' → '.$this->end->toDateString().' …');
        $this->runQueue();

        Carbon::setTestNow();

        $this->command?->info(sprintf(
            'Demo year seeded: %d orders, %d leads, %d attendance days (%d skipped steps). Logins use Password!123.',
            $this->counts['orders'], $this->counts['leads'], $this->counts['attendance'], $this->counts['skipped'],
        ));
    }

    // =================================================================== timeline

    private function at(Carbon $when, Closure $fn): void
    {
        if ($when->gt($this->end)) {
            return;
        }

        $this->queue->insert([$when->copy(), $fn], [-$when->getTimestamp(), -(++$this->seq)]);
    }

    private function runQueue(): void
    {
        $lastMonth = null;

        while (! $this->queue->isEmpty()) {
            [$when, $fn] = $this->queue->extract();
            $this->setTime($when);

            if ($this->command && $lastMonth !== $when->format('Y-m')) {
                $lastMonth = $when->format('Y-m');
                $this->command->line('  · '.$when->format('F Y'));
            }

            try {
                $fn();
            } catch (Throwable $e) {
                // A step the app's own rules refuse (e.g. balance too low) is
                // simply skipped, exactly as it would be in real use.
                $this->counts['skipped']++;
                if ($this->command?->getOutput()->isVerbose()) {
                    $this->command->warn('    skipped at '.$when->toDateTimeString().': '.$e->getMessage());
                }
            }
        }
    }

    private function setTime(Carbon $when): void
    {
        Carbon::setTestNow($when);
    }

    /** A moment inside month $m (0 = first month of the demo year). */
    private function monthStart(int $m): Carbon
    {
        return $this->start->copy()->startOfMonth()->addMonths($m);
    }

    private function randomTime(Carbon $day, int $fromHour = 9, int $toHour = 21): Carbon
    {
        return $day->copy()->setTime(mt_rand($fromHour, $toHour), mt_rand(0, 59), mt_rand(0, 59));
    }

    private function chance(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    private function pick(array $items): mixed
    {
        return $items[array_rand($items)];
    }

    private function service(string $class): object
    {
        return app($class);
    }

    // =================================================================== setup

    private function prepareCatalogue(): void
    {
        $order = (int) Product::max('display_order');

        foreach (D::catalogueProducts() as $i => $p) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($p['category'])],
                ['name' => $p['category'], 'status' => 'active'],
            );

            Product::updateOrCreate(['slug' => $p['slug']], [
                'name' => $p['name'],
                'category' => $p['category'],
                'category_id' => $category->id,
                'sku' => $p['sku'],
                'badge' => $p['badge'],
                'price' => $p['offer'],
                'mrp' => $p['price'],
                'offer_price' => $p['offer'],
                'stock' => 500,
                'rating' => mt_rand(42, 49) / 10,
                'review_count' => mt_rand(24, 310),
                'short_description' => $p['short'],
                'long_description' => $p['short']."\n\nMade in India under GMP-certified conditions. Sold exclusively through Global Life VIP partners.",
                'main_image' => self::PHOTOS.'/products/'.$p['slug'].'.jpg',
                'gallery' => [],
                'is_featured' => $i < 3,
                'status' => 'active',
                'display_order' => $order + $i + 1,
                'meta_title' => $p['name'],
                'meta_description' => $p['short'],
            ]);
        }

        $this->catalogue = Product::where('status', 'active')->get()
            ->filter(fn (Product $p) => $p->sellingPrice() > 0)->values();
    }

    private function prepareHr(): void
    {
        foreach (D::holidays() as $date => $name) {
            Holiday::firstOrCreate(['date' => $date], ['name' => $name]);
        }

        foreach (D::departments() as $dept => $designations) {
            $department = Department::firstOrCreate(['name' => $dept], ['is_active' => true]);
            foreach ($designations as $name) {
                Designation::firstOrCreate(['department_id' => $department->id, 'name' => $name], ['is_active' => true]);
            }
        }

        if (! Shift::where('is_default', true)->exists()) {
            Shift::create([
                'name' => 'General (10 AM - 7 PM)', 'start_time' => '10:00:00', 'end_time' => '19:00:00',
                'grace_minutes' => 15, 'half_day_minutes' => 240, 'full_day_minutes' => 480,
                'weekly_offs' => [0], 'is_default' => true,
            ]);
        }
    }

    // =================================================================== cleanup

    private function cleanup(): void
    {
        $staffEmails = collect([...D::admins(), ...D::subAdmins(), ...D::branchManagers(), ...D::commissionPartners(), ...D::employees()])
            ->map(fn (array $p) => $this->staffEmail($p['key']))->all();

        $demo = '%@'.D::DEMO_DOMAIN;
        $userIds = User::whereIn('email', $staffEmails)->orWhere('email', 'like', $demo)->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($userIds, $demo) {
            $siteIds = VipMicrosite::whereIn('user_id', $userIds)->pluck('id');

            Order::whereIn('user_id', $userIds)->orWhereIn('fulfilled_by_microsite_id', $siteIds)
                ->orWhereIn('id', DB::table('order_items')->whereIn('seller_microsite_id', $siteIds)->select('order_id'))
                ->get()->each(function (Order $order) {
                    $order->items()->delete();
                    $order->delete();
                });

            Lead::where('email', 'like', $demo)->orWhereIn('vip_microsite_id', $siteIds)->delete();

            $employeeIds = Employee::whereIn('user_id', $userIds)->pluck('id');
            Asset::whereIn('asset_tag', $this->assetTags())->delete();
            Announcement::whereIn('title', array_column($this->announcements(), 1))->delete();
            DB::table('payslips')->whereIn('employee_id', $employeeIds)->delete();
            PayrollRun::whereDoesntHave('payslips')->delete();

            // Deepest first: VIP members (their microsites take activation
            // ledgers and renewals with them), then everyone else.
            foreach (['vip_member', 'customer', 'employee', 'commission_partner', 'branch_manager', 'sub_admin', 'admin'] as $role) {
                User::whereIn('id', $userIds)->role($role)->get()->each(function (User $user) {
                    $this->deleteKycFiles($user);
                    DB::table('activity_log')->where('causer_type', User::class)->where('causer_id', $user->id)->delete();
                    $user->delete();
                });
            }

            User::whereIn('id', $userIds)->get()->each->delete();
        });
    }

    private function deleteKycFiles(User $user): void
    {
        Storage::disk('kyc')->deleteDirectory("members/{$user->id}");

        $employeeId = Employee::where('user_id', $user->id)->value('id');
        if ($employeeId) {
            Storage::disk('kyc')->deleteDirectory("hr/{$employeeId}");
        }
    }

    // =================================================================== people

    private function staffEmail(string $key): string
    {
        return str_replace('-', '.', $key).'@'.D::STAFF_DOMAIN;
    }

    /**
     * Profile + KYC photo, the way MemberProfileService stores them.
     *
     * @param  array<string, mixed>  $p
     */
    private function profile(User $user, array $p): void
    {
        $photo = $this->copyPhotoToKyc($p['key'], "members/{$user->id}/photo-".Str::random(16).'.jpg');
        $female = ($p['gender'] ?? 'male') === 'female';

        UserProfile::updateOrCreate(['user_id' => $user->id], [
            'salutation' => $female ? 'Ms.' : 'Mr.',
            'relation_type' => $female ? 'D/O' : 'S/O',
            'relative_name' => $this->pick(['Ramesh', 'Suresh', 'Mahesh', 'Rajendra', 'Anil', 'Vinod', 'Prakash']).' '.Str::afterLast($p['name'], ' '),
            'gender' => $female ? 'female' : 'male',
            'date_of_birth' => $p['dob'] ?? '19'.mt_rand(80, 98).'-0'.mt_rand(1, 9).'-'.mt_rand(10, 28),
            'blood_group' => $this->pick(['A+', 'B+', 'O+', 'AB+', 'O-', 'B-']),
            'address_line1' => $p['address'] ?? null,
            'city' => $p['city'] ?? null,
            'state' => $p['state'] ?? null,
            'pincode' => $p['pincode'] ?? null,
            'nominee_name' => ($female ? 'Rahul ' : 'Priya ').Str::afterLast($p['name'], ' '),
            'nominee_relation' => 'Spouse',
            'nominee_date_of_birth' => '19'.mt_rand(82, 96).'-0'.mt_rand(1, 9).'-'.mt_rand(10, 28),
            'nominee_mobile' => '9'.mt_rand(100000000, 999999999),
            'pan_number' => strtoupper(Str::random(3)).'P'.strtoupper(substr(Str::afterLast($p['name'], ' '), 0, 1)).mt_rand(1000, 9999).strtoupper(Str::random(1)),
            'photo_path' => $photo,
        ]);
    }

    private function copyPhotoToKyc(string $personKey, string $target): ?string
    {
        $source = self::PHOTOS."/people/{$personKey}.jpg";

        if (! Storage::disk('public')->exists($source)) {
            return null;
        }

        Storage::disk('kyc')->put($target, Storage::disk('public')->get($source));

        return $target;
    }

    private function planNetwork(): void
    {
        $t = $this->start->copy()->setTime(10, 0);

        $this->at($t, function () {
            foreach (D::admins() as $p) {
                $user = User::create([
                    'name' => $p['name'], 'email' => $this->staffEmail($p['key']), 'mobile' => $p['mobile'],
                    'password' => Hash::make(D::PASSWORD), 'status' => 'active', 'created_by' => $this->superAdmin->id,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole('admin');
                $this->profile($user, $p);
                $this->users[$p['key']] = $user;
            }
        });

        foreach (D::subAdmins() as $p) {
            $this->at($this->monthStart($p['month'])->addDays($p['month'] ? 4 : 0)->max($t->copy())->addMinutes(20), function () use ($p) {
                $user = $this->service(SubAdminService::class)->createSubAdmin([
                    'name' => $p['name'], 'email' => $this->staffEmail($p['key']), 'mobile' => $p['mobile'], 'password' => D::PASSWORD,
                ], $this->superAdmin);

                $permissions = collect($p['modules'])
                    ->flatMap(fn (string $module) => PermissionMatrixService::permissionsForModule($module))->all();
                $user->syncPermissions($permissions);
                $this->profile($user, $p);
                $this->users[$p['key']] = $user;
            });
        }

        foreach (D::branchManagers() as $i => $p) {
            $this->at($t->copy()->addHours(2 + $i), function () use ($p) {
                $cityIds = array_map(fn (string $slug) => $this->cities[$slug]->id, $p['cities']);
                $user = $this->service(BranchManagerService::class)->createBranchManager([
                    'name' => $p['name'], 'email' => $this->staffEmail($p['key']), 'mobile' => $p['mobile'], 'password' => D::PASSWORD,
                ], $cityIds, $p['percent'], $this->users[$p['owner']] ?? $this->superAdmin);

                $user->syncPermissions(BranchPermissionMatrixService::permissionsForModule('commission-partners'));
                $this->profile($user, $p);
                $this->users[$p['key']] = $user;
            });
        }

        foreach (D::commissionPartners() as $i => $p) {
            $when = $p['month'] === 0 ? $t->copy()->addDays(1)->addHours($i) : $this->monthStart($p['month'])->addDays(2)->setTime(11, 0);

            $this->at($when, function () use ($p) {
                $user = $this->service(CommissionPartnerService::class)->createPartner([
                    'name' => $p['name'], 'email' => $this->staffEmail($p['key']), 'mobile' => $p['mobile'], 'password' => D::PASSWORD,
                ], $p['percent'], $this->users[$p['bm']]);

                $user->cities()->syncWithoutDetaching([$this->cities[Str::lower($p['city'])]->id]);
                $this->profile($user, $p);
                $this->users[$p['key']] = $user;
            });
        }

        foreach (D::businesses() as $b) {
            $this->biz[$b['key']] = $b;
            $day = $this->monthStart($b['month'])->addDays(mt_rand(5, 24));
            $this->at($day->max($t->copy()->addDays(3))->setTime(mt_rand(11, 17), mt_rand(0, 59)), fn () => $this->createBusiness($b));
        }
    }

    /** @param array<string, mixed> $b */
    private function createBusiness(array $b): void
    {
        $partner = $this->users[$b['partner']];
        $email = $b['key'].'@'.D::DEMO_DOMAIN;

        $member = $this->service(VipMemberService::class)->createMember([
            'name' => $b['owner']['name'],
            'email' => $email,
            'password' => D::PASSWORD,
            'vip_plan_id' => $this->plans[$b['plan']]->id,
            'business_name' => $b['name'],
            'description' => $b['short'],
        ], $partner, $this->cities[$b['city']]);

        $member->update(['mobile' => $b['owner']['mobile']]);
        [$state] = D::localities()[$b['city']];
        $this->profile($member, $b['owner'] + ['address' => Str::before($b['address'], ','), 'city' => $this->cities[$b['city']]->name, 'state' => $state, 'pincode' => substr($b['address'], -6)]);

        $site = $member->vipMicrosite()->first();
        $dir = self::PHOTOS.'/'.$b['key'];
        [$open, $close, $off] = $b['hours'];

        $site->update([
            'business_category' => $b['category'],
            'business_sub_category' => $b['sub'],
            'owner_name' => $b['owner']['name'],
            'short_description' => $b['short'],
            'description' => $b['about'],
            'establishment_year' => $b['year'],
            'gst_no' => sprintf('%02d', mt_rand(9, 36)).strtoupper(Str::random(5)).mt_rand(1000, 9999).strtoupper(Str::random(1)).'1Z'.mt_rand(1, 9),
            'logo_path' => $this->logo($b),
            'cover_banner_path' => "{$dir}/about.jpg",
            'business_email' => $email,
            'phone_number' => $b['phone'],
            'whatsapp_number' => $b['phone'],
            'address' => $b['address'],
            'google_map_url' => 'https://maps.google.com/?q='.urlencode($b['name'].', '.$this->cities[$b['city']]->name),
            'business_hours' => collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
                ->mapWithKeys(fn (string $d) => [$d => ['open' => $open, 'close' => $close, 'closed' => in_array($d, $off, true)]])->all(),
            'facebook_url' => 'https://facebook.com/',
            'instagram_url' => 'https://instagram.com/',
            'module_visibility' => ['videos' => false] + BusinessModules::defaults(),
        ]);

        $site->banners()->create(['device' => 'desktop', 'image_path' => "{$dir}/banner.jpg", 'heading' => $b['name'], 'subheading' => $b['short'], 'button_text' => 'Enquire Now', 'button_link' => '#enquiry', 'sort_order' => 0, 'is_visible' => true]);
        $site->banners()->create(['device' => 'desktop', 'image_path' => "{$dir}/about.jpg", 'heading' => $b['sub'], 'subheading' => 'Serving '.$this->cities[$b['city']]->name.' since '.$b['year'].'.', 'button_text' => 'Call Us', 'button_link' => 'tel:'.$b['phone'], 'sort_order' => 1, 'is_visible' => true]);

        $images = collect(Storage::disk('public')->files($dir))->filter(fn ($f) => str_ends_with($f, '.jpg'))->values();
        $gallery = $images->filter(fn ($f) => str_contains($f, 'gallery-'))->values();

        foreach ($b['services'] as $i => [$name, $category, $mrp, $offer, $short]) {
            $site->services()->create([
                'name' => $name, 'slug' => Str::slug($name), 'category' => $category,
                'short_description' => $short, 'long_description' => $short,
                'image_path' => $gallery->isNotEmpty() ? $gallery[$i % $gallery->count()] : "{$dir}/about.jpg",
                'mrp' => $mrp ?: null, 'offer_price' => $offer ?: null,
                'discount_percent' => $mrp && $offer ? round(($mrp - $offer) / $mrp * 100) : null,
                'show_pricing' => $mrp > 0, 'show_book_now' => true, 'status' => 'published',
                'is_featured' => $i < 2, 'sort_order' => $i, 'meta_title' => $name, 'meta_description' => $short,
            ]);
        }

        foreach ($images as $i => $path) {
            $site->galleryItems()->create(['image_path' => $path, 'title' => $b['name'], 'sort_order' => $i, 'is_visible' => true]);
        }

        foreach ($b['faqs'] as $i => [$q, $a]) {
            $site->faqs()->create(['question' => $q, 'answer' => $a, 'sort_order' => $i, 'is_visible' => true]);
        }

        foreach ($b['own'] ?? [] as $i => [$name, $category, $mrp, $offer, $stock, $image]) {
            BusinessProduct::create([
                'vip_microsite_id' => $site->id, 'name' => $name, 'slug' => Str::slug($name), 'category' => $category,
                'short_description' => $name.' from '.$b['name'].'.', 'long_description' => $b['short'],
                'image_path' => "{$dir}/{$image}.jpg", 'mrp' => $mrp, 'offer_price' => $offer,
                'discount_percent' => round(($mrp - $offer) / $mrp * 100), 'show_pricing' => true,
                'sku' => strtoupper(substr($b['key'], 0, 3)).'-'.str_pad((string) ($i + 101), 4, '0', STR_PAD_LEFT),
                'stock' => $stock * 12, 'brand' => $b['name'], 'status' => 'published', 'is_featured' => $i < 2,
                'sort_order' => $i, 'meta_title' => $name, 'meta_description' => $name,
            ]);
        }

        // Company catalogue shown on the page — what shoppers buy and the upline earns on.
        foreach ($this->catalogue as $i => $product) {
            DB::table('vip_products')->insertOrIgnore([
                'vip_microsite_id' => $site->id, 'product_id' => $product->id, 'is_visible' => true,
                'is_featured' => $i < 3, 'display_order' => $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->sites[$b['key']] = $site;
        $this->users['vip:'.$b['key']] = $member;

        // Reviews trickle in over the following months.
        foreach ($b['reviews'] as $i => [$name, $rating, $text]) {
            $this->at(now()->addDays(10 + $i * mt_rand(12, 40))->setTime(mt_rand(10, 20), mt_rand(0, 59)), function () use ($site, $name, $rating, $text, $i) {
                $site->reviews()->create([
                    'customer_name' => $name, 'rating' => $rating, 'review_text' => $text, 'review_date' => now()->toDateString(),
                    'is_verified' => true, 'is_featured' => $i === 0, 'status' => 'approved',
                ]);
            });
        }

        $this->scheduleRenewal($b['key'], 0);
    }

    private function scheduleRenewal(string $key, int $step): void
    {
        $choice = $this->biz[$key]['renewals'][$step] ?? null;
        $site = $this->sites[$key]->fresh();

        if ($choice === null || ! $site->plan_expires_at) {
            return;
        }

        $when = $site->plan_expires_at->copy()->subDays(mt_rand(2, 12))->setTime(mt_rand(11, 18), mt_rand(0, 59));

        $this->at($when, function () use ($key, $step, $choice) {
            $site = $this->sites[$key]->fresh();
            $partner = $this->users[$this->biz[$key]['partner']];

            if ($choice === 'reject') {
                $this->service(VipRenewalService::class)->reject($site, $partner, 'Owner decided not to continue the online page this year.');

                return;
            }

            $this->service(VipRenewalService::class)->approve($site, $partner, $this->plans[$choice], 'Renewal fee collected by cheque/UPI.');
            $this->scheduleRenewal($key, $step + 1);
        });
    }

    private function logo(array $b): string
    {
        $palette = ['#b91c1c', '#c2410c', '#a16207', '#15803d', '#0f766e', '#0369a1', '#4338ca', '#7e22ce', '#be185d', '#334155'];
        $c = $palette[crc32($b['key']) % count($palette)];
        $words = preg_split('/[\s&]+/', preg_replace('/[^A-Za-z &]/', '', $b['name']), -1, PREG_SPLIT_NO_EMPTY);
        $initials = strtoupper(substr($words[0], 0, 1).substr($words[1] ?? '', 0, 1));

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <rect width="400" height="400" rx="88" fill="{$c}"/>
  <circle cx="200" cy="200" r="150" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="6"/>
  <text x="200" y="245" font-size="140" font-weight="800" fill="#fff" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif">{$initials}</text>
</svg>
SVG;
        $path = self::PHOTOS."/{$b['key']}/logo.svg";
        Storage::disk('public')->put($path, $svg);

        // Also keep a copy in the committed folder (storage/app/public), which is
        // what reaches the live server; the public disk may point outside the repo.
        $committed = \App\Support\UploadsRoot::legacy().DIRECTORY_SEPARATOR.$path;
        if (! is_file($committed) && is_dir(dirname($committed))) {
            file_put_contents($committed, $svg);
        }

        return $path;
    }

    // =================================================================== HR

    private function planEmployees(): void
    {
        $leaveTypes = LeaveType::pluck('id', 'code');

        foreach (D::employees() as $p) {
            $joined = Carbon::parse($p['joined']);
            $created = $joined->copy()->subDays(3)->setTime(15, 0);

            $this->at($created, fn () => $this->createEmployee($p));
            $this->planLeaves($p, $leaveTypes->all());

            if (isset($p['exit'])) {
                [$exitDate, $reason] = $p['exit'];
                $this->at(Carbon::parse($exitDate)->subMonth()->setTime(12, 0), function () use ($p, $exitDate, $reason) {
                    $this->service(EmployeeService::class)->changeStatus($this->employees[$p['key']], 'on_notice', $exitDate, $reason);
                });
                $this->at(Carbon::parse($exitDate)->setTime(19, 30), function () use ($p, $exitDate, $reason) {
                    $employee = $this->employees[$p['key']];
                    foreach ($employee->assets()->get() as $asset) {
                        $this->service(AssetService::class)->returnAsset($asset, $exitDate, 'Returned in good condition at exit.');
                    }
                    $this->service(EmployeeService::class)->changeStatus($employee, 'resigned', $exitDate, $reason);
                });
            }
        }

        // One attendance pass per evening.
        for ($day = $this->start->copy(); $day->lt($this->end->copy()->startOfDay()); $day->addDay()) {
            $date = $day->copy();
            $this->at($date->copy()->setTime(20, 30), fn () => $this->markAttendance($date));
        }

        $this->planPayroll();
        $this->planHrExtras();
    }

    /** @param array<string, mixed> $p */
    private function createEmployee(array $p): void
    {
        $department = Department::where('name', $p['dept'])->first();
        $designation = Designation::where('department_id', $department?->id)->where('name', $p['designation'])->first();
        $gross = $p['gross'];
        $basic = round($gross * 0.5);
        $hra = round($gross * 0.2);
        $conveyance = 1600;
        $other = $gross >= 40000 ? 2500 : 0;

        $result = $this->service(EmployeeService::class)->create(
            ['name' => $p['name'], 'email' => $this->staffEmail($p['key']), 'mobile' => $p['mobile'], 'password' => D::PASSWORD],
            [
                'department_id' => $department?->id,
                'designation_id' => $designation?->id,
                'shift_id' => Shift::where('is_default', true)->value('id'),
                'city_id' => $this->cities[$p['city']]->id,
                'employment_type' => $p['type'],
                'joining_date' => $p['joined'],
                'probation_end_date' => Carbon::parse($p['joined'])->addMonths(6)->toDateString(),
                'confirmation_date' => Carbon::parse($p['joined'])->addMonths(6)->lte($this->end) ? Carbon::parse($p['joined'])->addMonths(6)->toDateString() : null,
                'date_of_birth' => $p['dob'],
                'gender' => $p['gender'],
                'blood_group' => $p['blood'],
                'marital_status' => $p['marital'],
                'current_address' => mt_rand(1, 220).', '.$this->pick(D::localities()[$p['city']][1])[1].', '.$this->cities[$p['city']]->name,
                'permanent_address' => mt_rand(1, 90).', '.$this->pick(['Civil Lines', 'Station Road', 'Rajendra Nagar', 'Shastri Nagar']).', '.$this->pick(['Kanpur', 'Prayagraj', 'Varanasi', 'Gorakhpur', 'Bareilly']),
                'emergency_contact_name' => ($p['gender'] === 'female' ? 'Rakesh ' : 'Sunita ').Str::afterLast($p['name'], ' '),
                'emergency_contact_relation' => $p['marital'] === 'married' ? 'Spouse' : 'Parent',
                'emergency_contact_phone' => '9'.mt_rand(100000000, 999999999),
                'bank_name' => $this->pick(['State Bank of India', 'HDFC Bank', 'ICICI Bank', 'Punjab National Bank']),
                'bank_account_name' => $p['name'],
                'bank_account_number' => (string) mt_rand(10000000, 99999999).mt_rand(1000, 9999),
                'bank_ifsc' => $this->pick(['SBIN0001234', 'HDFC0000456', 'ICIC0000789', 'PUNB0123400']),
                'upi_id' => str_replace('-', '', $p['key']).'@okaxis',
                'pan_number' => strtoupper(Str::random(3)).'P'.strtoupper(substr(Str::afterLast($p['name'], ' '), 0, 1)).mt_rand(1000, 9999).strtoupper(Str::random(1)),
                'uan_number' => '10'.mt_rand(1000000000, 9999999999),
                'esi_number' => $gross <= 21000 ? (string) mt_rand(1000000000, 9999999999) : null,
            ],
            [
                'basic' => $basic, 'hra' => $hra, 'conveyance' => $conveyance, 'other_allowance' => $other,
                'special_allowance' => $gross - $basic - $hra - $conveyance - $other,
                'pf_enabled' => $p['type'] !== 'contract', 'esi_enabled' => $gross <= 21000,
                'professional_tax' => 0, 'tds' => $gross >= 60000 ? round($gross * 0.04) : 0,
            ],
            null,
            $this->superAdmin,
        );

        $employee = $result['employee'];
        $photo = $this->copyPhotoToKyc($p['key'], "hr/{$employee->id}/photo/".Str::random(20).'.jpg');
        // Every manager joined before their reports, so they already exist.
        $employee->update([
            'photo_path' => $photo,
            'reporting_manager_id' => $p['manager'] ? ($this->employees[$p['manager']] ?? null)?->id : null,
        ]);

        $this->employees[$p['key']] = $employee;
        $this->users['emp:'.$p['key']] = $employee->user;
    }

    /**
     * Decide each employee's leave for the year up front, so the evening
     * attendance pass knows which days they are away.
     *
     * @param  array<string, mixed>  $p
     * @param  array<string, int>  $types
     */
    private function planLeaves(array $p, array $types): void
    {
        $from = Carbon::parse($p['joined'])->max($this->start->copy())->addDays(20);
        $until = isset($p['exit']) ? Carbon::parse($p['exit'][0])->subDays(10) : $this->end->copy()->addDays(20);
        $this->leaveDays[$p['key']] = [];
        $this->regularizeDays[$p['key']] = [];

        if ($from->gte($until)) {
            return;
        }

        $span = (int) $from->diffInDays($until);
        $plan = [];
        $longTenure = Carbon::parse($p['joined'])->lt($this->start->copy()->subYear());

        foreach (range(1, max(1, intdiv($span, 70))) as $_) {
            $plan[] = ['SL', 1, false];
        }
        foreach (range(1, max(1, intdiv($span, 55))) as $_) {
            $plan[] = ['CL', mt_rand(1, 2), $this->chance(12)];
        }
        if ($longTenure && $span > 200) {
            $plan[] = ['EL', mt_rand(3, 5), false];
        }
        if ($this->chance(25)) {
            $plan[] = ['CL', 1, false, true]; // half day
        }

        foreach ($plan as $leave) {
            [$code, $length, $rejected] = $leave;
            $halfDay = $leave[3] ?? false;

            for ($try = 0; $try < 15; $try++) {
                $day = $from->copy()->addDays(mt_rand(0, $span));
                $dates = $this->workingDates($day, $length);

                if (! $dates || collect($dates)->contains(fn ($d) => isset($this->leaveDays[$p['key']][$d]))
                    || substr($dates[0], 0, 4) !== substr(end($dates), 0, 4)) {
                    continue;
                }

                if (! $rejected) {
                    foreach ($dates as $d) {
                        $this->leaveDays[$p['key']][$d] = $halfDay ? 'half' : true;
                    }
                }

                $this->scheduleLeave($p, $types[$code] ?? null, $dates, $code, $rejected, $halfDay);
                break;
            }
        }

        // A few forgotten check-ins, put right through a regularization.
        foreach (range(1, mt_rand(1, 3)) as $_) {
            $day = $from->copy()->addDays(mt_rand(0, $span));
            $dates = $this->workingDates($day, 1);
            if ($dates && ! isset($this->leaveDays[$p['key']][$dates[0]])) {
                $this->regularizeDays[$p['key']][$dates[0]] = true;
            }
        }
    }

    /** @return list<string>|null consecutive Mon–Sat non-holiday dates */
    private function workingDates(Carbon $first, int $length): ?array
    {
        $holidays = D::holidays();
        $dates = [];
        $day = $first->copy();

        while (count($dates) < $length) {
            if ($day->isSunday() || isset($holidays[$day->toDateString()])) {
                if ($dates === []) {
                    $day->addDay();

                    continue;
                }
                break;
            }
            $dates[] = $day->toDateString();
            $day->addDay();
        }

        return count($dates) === $length ? $dates : null;
    }

    /** @param list<string> $dates */
    private function scheduleLeave(array $p, ?int $typeId, array $dates, string $code, bool $rejected, bool $halfDay): void
    {
        if (! $typeId) {
            return;
        }

        $firstDay = Carbon::parse($dates[0]);
        $applyAt = $code === 'SL' ? $firstDay->copy()->setTime(8, mt_rand(5, 50)) : $firstDay->copy()->subDays(mt_rand(3, 12))->setTime(mt_rand(10, 18), mt_rand(0, 59));
        $reviewAt = $code === 'SL' ? $firstDay->copy()->setTime(11, mt_rand(0, 59)) : $applyAt->copy()->addDay()->setTime(mt_rand(10, 13), mt_rand(0, 59));

        $reasons = [
            'SL' => ['Down with viral fever, doctor advised rest.', 'Severe migraine since morning.', 'Stomach infection, visiting the doctor.', 'High fever and cold.'],
            'CL' => ['Family function in hometown.', 'Personal work at the bank and RTO.', 'Sister’s engagement ceremony.', 'Child’s parent-teacher meeting and school admission.', 'House shifting.'],
            'EL' => ['Family vacation to Nainital.', 'Visiting native place for a family wedding.', 'Annual family trip to Goa.'],
        ];

        $holder = new \stdClass;

        $this->at($applyAt, function () use ($p, $typeId, $dates, $halfDay, $code, $reasons, $holder) {
            $holder->request = $this->service(LeaveService::class)->apply($this->employees[$p['key']], [
                'leave_type_id' => $typeId,
                'from_date' => $dates[0],
                'to_date' => end($dates),
                'is_half_day' => $halfDay,
                'reason' => $this->pick($reasons[$code]),
            ]);
        });

        $this->at($reviewAt, function () use ($p, $rejected, $holder) {
            if (empty($holder->request)) {
                return;
            }
            $reviewer = $this->reviewerFor($p);
            $rejected
                ? $this->service(LeaveService::class)->reject($holder->request, $reviewer, 'Quarter-end closing that week — please plan for the following week.')
                : $this->service(LeaveService::class)->approve($holder->request, $reviewer);
        });
    }

    private function reviewerFor(array $p): User
    {
        $manager = $p['manager'] ? ($this->employees[$p['manager']] ?? null) : null;

        return $manager?->user ?? ($this->employees['ritu-agarwal'] ?? null)?->user ?? $this->superAdmin;
    }

    private function markAttendance(Carbon $date): void
    {
        $calendar = $this->service(WorkCalendarService::class);
        $attendance = $this->service(AttendanceService::class);
        $ds = $date->toDateString();

        foreach (D::employees() as $p) {
            $employee = $this->employees[$p['key']] ?? null;

            if (! $employee || Carbon::parse($p['joined'])->gt($date)
                || (isset($p['exit']) && Carbon::parse($p['exit'][0])->lt($date))
                || ! $calendar->isWorkingDay($employee, $date)) {
                continue;
            }

            $leave = $this->leaveDays[$p['key']][$ds] ?? null;
            if ($leave === true) {
                continue;
            }

            if (isset($this->regularizeDays[$p['key']][$ds])) {
                $this->scheduleRegularization($p, $date);

                continue;
            }

            if ($leave !== 'half' && $this->chance(1)) {
                $attendance->saveByAdmin($employee, ['date' => $ds, 'status' => 'absent', 'note' => 'Did not report, no intimation.'], $this->superAdmin, 'admin');
                $this->counts['attendance']++;

                continue;
            }

            // Field staff start a little later and finish on the road.
            $lateProne = in_array($p['key'], ['aman-khan', 'harsh-vardhan', 'tanya-kapoor'], true) ? 14 : 5;
            $in = $this->chance($lateProne) ? mt_rand(616, 655) : mt_rand(570, 614); // minutes after midnight
            $out = $in + ($leave === 'half' ? mt_rand(250, 290) : mt_rand(525, 580));

            if ($leave !== 'half' && $this->chance(2)) {
                $out = $in + mt_rand(260, 400); // left early — half day
            }

            $attendance->saveByAdmin($employee, [
                'date' => $ds,
                'check_in' => sprintf('%02d:%02d', intdiv($in, 60), $in % 60),
                'check_out' => sprintf('%02d:%02d', intdiv(min($out, 1430), 60), min($out, 1430) % 60),
                'note' => ! empty($p['field']) && $this->chance(30) ? 'Partner visits in '.$this->pick(['Gomti Nagar', 'Aliganj', 'Hazratganj', 'Jhansi city', 'Indira Nagar']) : null,
            ], $employee->user, 'self');
            $this->counts['attendance']++;
        }
    }

    private function scheduleRegularization(array $p, Carbon $date): void
    {
        $holder = new \stdClass;

        $this->at($date->copy()->addDay()->setTime(10, 40), function () use ($p, $date, $holder) {
            $holder->request = $this->service(AttendanceService::class)->requestRegularization($this->employees[$p['key']], [
                'date' => $date->toDateString(),
                'check_in_time' => sprintf('09:%02d', mt_rand(45, 59)),
                'check_out_time' => sprintf('19:%02d', mt_rand(0, 30)),
                'reason' => $this->pick(['Forgot to check in — was at the office all day.', 'Attendance app did not load in the morning.', 'Was at a partner meeting first thing and forgot to mark attendance.']),
            ]);
        });

        $this->at($date->copy()->addDays(2)->setTime(12, 15), function () use ($p, $holder) {
            if (! empty($holder->request) && $holder->request->fresh()->status === 'pending') {
                $this->service(AttendanceService::class)->approveRegularization($holder->request, $this->reviewerFor($p), 'Verified with the team.');
            }
        });
    }

    private function planPayroll(): void
    {
        for ($m = 1; ; $m++) {
            $month = $this->monthStart($m - 1);
            $runAt = $month->copy()->addMonth()->startOfMonth()->setTime(11, 0);

            if ($runAt->gt($this->end) || $m > 13) {
                break;
            }
            if ($month->lt($this->start->copy()->addDays(5))) {
                continue; // the first, partial month has no attendance before the start
            }

            $key = $month->format('Y-m');

            $this->at($runAt, function () use ($key) {
                $run = $this->service(PayrollService::class)->generate($key, $this->employees['ritu-agarwal']->user ?? $this->superAdmin);

                foreach ($run->payslips()->with('employee.user')->get() as $payslip) {
                    $email = $payslip->employee->user->email;
                    $adjust = [];

                    if (in_array($email, [$this->staffEmail('rohan-mehta'), $this->staffEmail('gurpreet-singh')], true)) {
                        $adjust['incentive'] = mt_rand(18, 65) * 100; // partner sign-up incentive
                    }
                    if (str_ends_with($key, '-10')) {
                        $adjust['bonus'] = round((float) $payslip->basic * 0.5, -2); // Diwali bonus
                    }
                    if ($email === $this->staffEmail('aman-khan') && $this->chance(30)) {
                        $adjust['overtime'] = mt_rand(20, 50) * 100;
                    }

                    if ($adjust) {
                        $this->service(PayrollService::class)->adjust($payslip, $adjust);
                    }
                }
            });

            $this->at($runAt->copy()->addDays(2)->setTime(16, 30), function () use ($key) {
                $run = PayrollRun::where('month', $key)->first();
                if ($run?->isDraft()) {
                    $this->service(PayrollService::class)->finalize($run, $this->superAdmin);
                }
            });
        }
    }

    private function assetTags(): array
    {
        return array_column($this->assets(), 1);
    }

    /** @return list<array{0: string, 1: string, 2: string, 3: string, 4: int, 5: ?string}> name, tag, category, purchased, cost, holder */
    private function assets(): array
    {
        return [
            ['Dell Latitude 5440 Laptop', 'GLD-LT-0001', 'Laptop', '2024-03-12', 68500, 'deepak-sharma'],
            ['Lenovo ThinkPad E14 Laptop', 'GLD-LT-0002', 'Laptop', '2024-03-12', 61200, 'ritu-agarwal'],
            ['HP ProBook 440 G10 Laptop', 'GLD-LT-0003', 'Laptop', '2024-06-20', 58900, 'manish-gupta'],
            ['Apple MacBook Air M2', 'GLD-LT-0004', 'Laptop', '2024-09-02', 99900, 'aman-khan'],
            ['HP 245 G10 Laptop', 'GLD-LT-0005', 'Laptop', '2025-01-15', 41500, 'shruti-pandey'],
            ['Apple iMac 24" M3', 'GLD-DT-0001', 'Desktop', '2025-01-15', 134900, 'anjali-saxena'],
            ['Dell Vostro 3020 Desktop', 'GLD-DT-0002', 'Desktop', '2024-06-20', 38500, 'vivek-pathak'],
            ['Dell Vostro 3020 Desktop', 'GLD-DT-0003', 'Desktop', '2024-06-20', 38500, 'priyanka-mishra'],
            ['Dell Vostro 3020 Desktop', 'GLD-DT-0004', 'Desktop', '2025-02-01', 38500, 'neha-yadav'],
            ['Lenovo V15 Laptop', 'GLD-LT-0006', 'Laptop', '2026-02-25', 39900, 'tanya-kapoor'],
            ['Acer Aspire 3 Laptop', 'GLD-LT-0007', 'Laptop', '2026-06-10', 34900, 'pallavi-dubey'],
            ['Samsung Galaxy M34 Mobile', 'GLD-MB-0001', 'Mobile', '2024-05-10', 15999, 'rohan-mehta'],
            ['Samsung Galaxy M34 Mobile', 'GLD-MB-0002', 'Mobile', '2024-05-10', 15999, 'gurpreet-singh'],
            ['Jio Business SIM 98390 00121', 'GLD-SIM-0001', 'SIM Card', '2024-05-10', 0, 'rohan-mehta'],
            ['Jio Business SIM 98390 00122', 'GLD-SIM-0002', 'SIM Card', '2024-05-10', 0, 'gurpreet-singh'],
            ['Honda Activa 6G (UP32 MK 4471)', 'GLD-VH-0001', 'Vehicle', '2024-05-20', 82500, 'gurpreet-singh'],
            ['Redmi 13C Office Phone', 'GLD-MB-0003', 'Mobile', '2024-11-15', 8999, 'harsh-vardhan'],
            ['Godrej Ergonomic Chair', 'GLD-FN-0001', 'Furniture', '2024-03-01', 12500, null],
            ['HP LaserJet Pro MFP 4104', 'GLD-OT-0001', 'Other', '2024-03-01', 32900, null],
            ['Dell Latitude 3440 Laptop (spare)', 'GLD-LT-0008', 'Laptop', '2025-08-18', 52400, null],
        ];
    }

    private function announcements(): array
    {
        return [
            ['2025-12-01', 'Year-end closing schedule', "Accounts will close the books for 2025 on 31 December. Please submit all pending expense claims by 26 December.\n\nThe office stays closed on 25 December for Christmas."],
            ['2026-01-20', 'Republic Day celebration', 'Flag hoisting at 9:30 am on 26 January at the Gomti Nagar office, followed by breakfast. Families are welcome.'],
            ['2026-03-01', 'Holi holiday and office timing', 'The office is closed on 4 March for Holi. On 3 March we close at 4 pm — Happy Holi in advance!'],
            ['2026-04-01', 'New financial year — investment declarations', 'Please submit your FY 2026-27 investment declarations (80C, HRA, 80D) to HR by 20 April so TDS can be calculated correctly.'],
            ['2026-06-15', 'Welcome Pallavi Dubey', 'Please welcome Pallavi Dubey, who joins Operations as Data Entry Operator today.'],
            ['2026-08-10', 'Health check-up camp', 'A free health check-up camp (BP, sugar, lipid profile, eye test) will be held at the office on 22 August from 10 am to 2 pm.'],
            ['2026-10-25', 'Diwali bonus and office celebration', "The Diwali bonus will be paid with the October salary. Office Diwali celebration on 7 November at 4 pm — ethnic wear encouraged!\n\nThe office is closed on 9 November."],
        ];
    }

    private function planHrExtras(): void
    {
        foreach ($this->announcements() as [$date, $title, $body]) {
            $this->at(Carbon::parse($date)->setTime(10, 15), fn () => Announcement::create([
                'title' => $title, 'body' => $body, 'is_active' => true, 'published_by' => ($this->employees['ritu-agarwal'] ?? null)?->user_id,
            ]));
        }

        $this->at($this->start->copy()->setTime(9, 50), function () {
            foreach ($this->assets() as [$name, $tag, $category, $bought, $cost, $holder]) {
                $asset = Asset::create([
                    'name' => $name, 'asset_tag' => $tag, 'category' => $category,
                    'serial_number' => $category === 'SIM Card' ? null : strtoupper(Str::random(4)).mt_rand(100000, 999999),
                    'purchase_date' => $bought, 'purchase_cost' => $cost ?: null, 'status' => 'available',
                ]);

                $employee = $holder ? ($this->employees[$holder] ?? null) : null;
                if ($employee) {
                    $this->service(AssetService::class)->assign($asset, $employee, Carbon::parse($bought)->max(Carbon::parse($employee->joining_date))->toDateString());
                }
            }
        });

        // Late joiners get their kit on day one.
        foreach (['tanya-kapoor' => 'GLD-LT-0006', 'pallavi-dubey' => 'GLD-LT-0007'] as $key => $tag) {
            $joined = collect(D::employees())->firstWhere('key', $key)['joined'];
            $this->at(Carbon::parse($joined)->setTime(10, 30), function () use ($key, $tag, $joined) {
                $asset = Asset::where('asset_tag', $tag)->first();
                if ($asset && $asset->status === 'available') {
                    $this->service(AssetService::class)->assign($asset, $this->employees[$key], $joined);
                }
            });
        }

        // Salary advances, recovered through payroll.
        $this->at(Carbon::parse('2026-04-10 12:00'), fn () => $this->service(PayrollService::class)->issueAdvance(
            $this->employees['gurpreet-singh'], 20000, 5000, 'Medical emergency — father’s surgery', '2026-04-10', $this->superAdmin,
        ));
        $this->at(Carbon::parse('2026-08-05 12:00'), fn () => $this->service(PayrollService::class)->issueAdvance(
            $this->employees['vivek-pathak'], 12000, 3000, 'Two-wheeler down payment', '2026-08-05', $this->superAdmin,
        ));

        // Expense claims: field sales every month, managers now and then.
        $claimants = [
            'rohan-mehta' => [['Travel', 'Partner visits — Lucknow city', 650, 1900], ['Fuel', 'Fuel for partner visits', 1200, 3200], ['Client Meeting', 'Lunch meeting with prospective partner', 450, 1400]],
            'gurpreet-singh' => [['Fuel', 'Fuel — Jhansi field visits', 1500, 3600], ['Travel', 'Bus fare Jhansi–Lucknow review meeting', 420, 900], ['Food', 'Meals during field tour', 300, 800]],
            'deepak-sharma' => [['Client Meeting', 'Branch Manager review dinner', 2400, 5200], ['Accommodation', 'Hotel stay — Pune branch visit', 3800, 6500]],
            'shruti-pandey' => [['Office Supplies', 'Standees and brochures for partner meet', 1800, 4200], ['Mobile / Internet', 'Data pack for social media live sessions', 299, 599]],
        ];

        foreach ($claimants as $key => $kinds) {
            $joined = Carbon::parse(collect(D::employees())->firstWhere('key', $key)['joined']);

            for ($m = 0; $m < 12; $m++) {
                $count = $key === 'deepak-sharma' || $key === 'shruti-pandey' ? (int) $this->chance(45) : mt_rand(1, 3);

                for ($i = 0; $i < $count; $i++) {
                    [$category, $title, $min, $max] = $this->pick($kinds);
                    $spent = $this->monthStart($m)->addDays(mt_rand(1, 26));
                    if ($spent->lt($this->start) || $spent->lt($joined)) {
                        continue;
                    }

                    $amount = mt_rand($min, $max);
                    $holder = new \stdClass;

                    $this->at($spent->copy()->addDays(mt_rand(0, 3))->setTime(18, mt_rand(0, 59)), function () use ($key, $category, $title, $amount, $spent, $holder) {
                        $holder->claim = $this->service(ExpenseService::class)->submit($this->employees[$key], [
                            'title' => $title, 'category' => $category, 'amount' => $amount,
                            'expense_date' => $spent->toDateString(), 'description' => 'Bills attached in the expense folder.',
                        ], null);
                    });

                    $reviewer = collect(D::employees())->firstWhere('key', $key);
                    $this->at($spent->copy()->addDays(mt_rand(4, 7))->setTime(12, mt_rand(0, 59)), function () use ($holder, $reviewer) {
                        if (empty($holder->claim)) {
                            return;
                        }
                        $this->chance(6)
                            ? $this->service(ExpenseService::class)->reject($holder->claim, $this->reviewerFor($reviewer), 'Bill not attached — please resubmit with the receipt.')
                            : $this->service(ExpenseService::class)->approve($holder->claim, $this->reviewerFor($reviewer));
                    });

                    $this->at($spent->copy()->addMonthNoOverflow()->startOfMonth()->addDays(6)->setTime(15, 0), function () use ($holder) {
                        if (! empty($holder->claim) && $holder->claim->fresh()->status === 'approved') {
                            $this->service(ExpenseService::class)->markPaid($holder->claim->fresh());
                        }
                    });
                }
            }
        }
    }

    // =================================================================== commerce

    private function shopper(string $city): array
    {
        $pool = &$this->shoppers[$city];
        $pool ??= [];

        // Most orders come from returning customers once a city has a few.
        if (count($pool) >= 6 && $this->chance(65)) {
            return $pool[array_rand($pool)];
        }

        $names = D::shopperNames();
        do {
            $first = $this->pick($names['first']);
            $last = $this->pick($names['last']);
            $email = Str::lower("{$first}.{$last}.{$city}").'@'.D::DEMO_DOMAIN;
        } while (collect($pool)->contains('email', $email) || User::where('email', $email)->exists());

        [$state, $places] = D::localities()[$city];
        [$pin, $area] = $this->pick($places);

        $user = User::create([
            'name' => "{$first} {$last}", 'email' => $email, 'mobile' => '9'.mt_rand(100000000, 999999999),
            'password' => Hash::make(D::PASSWORD), 'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('customer');

        $address = mt_rand(1, 180).', '.$this->pick(['Shanti Apartments', 'Green Park Society', 'Sai Residency', 'Lake View Towers', 'Gulmohar Enclave', 'Main Road']).', '.$area;

        Address::create([
            'user_id' => $user->id, 'name' => $user->name, 'phone' => $user->mobile, 'address' => $address,
            'city' => $this->cities[$city]->name, 'state' => $state, 'pincode' => $pin, 'is_default' => true,
        ]);

        return $pool[] = ['user' => $user, 'email' => $email, 'address' => $address, 'city' => $this->cities[$city]->name, 'state' => $state, 'pincode' => $pin];
    }

    private function planCommerce(): void
    {
        foreach (D::businesses() as $b) {
            for ($m = $b['month']; $m < 13; $m++) {
                $age = $m - $b['month'];
                $catalogue = mt_rand(2, 4) + intdiv($age, 2) + (in_array($m, [10, 11], true) ? 3 : 0); // festive season lift
                $own = isset($b['own']) ? mt_rand(4, 7) + $age : 0;

                for ($i = 0; $i < $catalogue + $own; $i++) {
                    $when = $this->randomTime($this->monthStart($m)->addDays(mt_rand(0, $this->monthStart($m)->daysInMonth - 1)), 8, 22);
                    $direct = $i >= $catalogue;
                    $this->at($when, fn () => $this->placeOrder($b['key'], $direct));
                }
            }
        }
    }

    private function placeOrder(string $key, bool $direct): void
    {
        $site = ($this->sites[$key] ?? null)?->fresh();

        if (! $site || ! $site->isLive() || $site->user->status !== 'active') {
            return;
        }

        $shopper = $this->shopper($this->biz[$key]['city']);
        $lines = collect();

        if ($direct) {
            $products = $site->products()->where('status', 'published')->get();
            foreach ($products->random(min($products->count(), mt_rand(1, 3))) as $product) {
                $qty = mt_rand(1, 3);
                $price = (float) $product->sellingPrice();
                $lines->push([
                    'product_id' => null, 'business_product_id' => $product->id, 'product_name' => $product->name,
                    'product_sku' => $product->sku, 'seller_id' => $site->id, 'unit_price' => $price, 'quantity' => $qty,
                    'line_total' => round($price * $qty, 2), 'product' => null, 'seller' => $site,
                ]);
            }
        } else {
            foreach ($this->catalogue->random(min($this->catalogue->count(), mt_rand(1, 3))) as $product) {
                $qty = $this->chance(80) ? 1 : 2;
                $price = (float) $product->sellingPrice();
                $lines->push([
                    'product_id' => $product->id, 'business_product_id' => null, 'product_name' => $product->name,
                    'product_sku' => $product->sku, 'seller_id' => $site->id, 'unit_price' => $price, 'quantity' => $qty,
                    'line_total' => round($price * $qty, 2), 'product' => $product, 'seller' => $site,
                ]);
            }
        }

        if ($lines->isEmpty()) {
            return;
        }

        $online = $this->chance(62);
        $subtotal = round($lines->sum('line_total'), 2);
        $data = [
            'customer_name' => $shopper['user']->name, 'customer_phone' => $shopper['user']->mobile,
            'address' => $shopper['address'], 'city' => $shopper['city'], 'state' => $shopper['state'], 'pincode' => $shopper['pincode'],
            'delivery_notes' => $this->chance(20) ? $this->pick(['Please call before delivery.', 'Leave with the security guard.', 'Deliver after 6 pm.']) : null,
        ] + ($online ? ['razorpay_order_id' => 'order_'.Str::random(14), 'razorpay_payment_id' => 'pay_'.Str::random(14), 'razorpay_signature' => hash('sha256', Str::random(32))] : []);

        // The real write path (order, items, pending upline commission, stock),
        // minus the session cart and payment gateway.
        $service = $this->service(OrderService::class);
        $order = (fn () => $this->persistOrder($data, $shopper['user'], $lines, ['subtotal' => $subtotal, 'shipping' => 0.0, 'total' => $subtotal], $online ? 'online' : 'cod', $online ? 'razorpay' : null))
            ->call($service);

        $this->counts['orders']++;
        $placed = now()->copy();

        if ($direct && $this->chance(5)) {
            $this->at($placed->copy()->addHours(mt_rand(2, 20)), fn () => $service->updateStatus($order->fresh(), $online ? 'refunded' : 'cancelled'));

            return;
        }

        $processing = $placed->copy()->addHours(mt_rand(1, 6));
        $dispatched = $processing->copy()->addHours($direct ? mt_rand(2, 8) : mt_rand(16, 30));
        $delivered = $dispatched->copy()->addHours($direct ? mt_rand(2, 20) : mt_rand(30, 90));

        $this->at($processing, fn () => $service->updateStatus($order->fresh(), 'processing'));
        $this->at($dispatched, fn () => $service->updateStatus($order->fresh(), 'dispatched'));
        $this->at($delivered->setTime(max(9, min(20, $delivered->hour)), mt_rand(0, 59)), fn () => $service->markDelivered($order->fresh()));
    }

    // =================================================================== leads

    private function planLeads(): void
    {
        $sources = ['homepage', 'homepage', 'contact_page', 'chatbot', 'microsite', 'microsite', 'microsite', 'product'];
        $messages = [
            'homepage' => ['I run a small shop and want an online page for my business. Please share the plans.', 'Interested in becoming a VIP member — what does the Premium plan include?', 'Please call me about listing my clinic.'],
            'contact_page' => ['Want to know about the Commission Partner opportunity in my city.', 'Please share details of your wellness product range for bulk purchase.', 'Need a call back regarding VIP membership.'],
            'chatbot' => ['How much does the Growth plan cost?', 'Can I sell my own products on the VIP page?', 'Is there a plan for a single shop?'],
            'microsite' => ['Please share your price list.', 'Do you deliver to my area?', 'I would like to book an appointment this weekend.', 'Please call me back with details.', 'Is there any offer running this month?'],
            'product' => ['Is this available in a larger pack?', 'Please share the ingredients and usage.', 'Want to order 10 units for gifting — any discount?'],
        ];
        $names = D::shopperNames();

        for ($m = 0; $m < 13; $m++) {
            $count = 10 + $m * 2 + mt_rand(0, 5);

            for ($i = 0; $i < $count; $i++) {
                $when = $this->randomTime($this->monthStart($m)->addDays(mt_rand(0, 27)), 8, 22);

                $this->at($when, function () use ($sources, $messages, $names) {
                    $source = $this->pick($sources);
                    $live = collect($this->sites)->filter(fn (VipMicrosite $s) => $s->fresh()->isLive());

                    if ($source === 'microsite' && $live->isEmpty()) {
                        $source = 'homepage';
                    }

                    $site = $source === 'microsite' ? $live->random() : null;
                    $citySlug = $site ? $this->biz[array_search($site, $this->sites, true)]['city'] : $this->pick(array_keys($this->cities));
                    $first = $this->pick($names['first']);
                    $last = $this->pick($names['last']);
                    $age = (int) now()->diffInDays($this->end);

                    $status = match (true) {
                        $age > 30 => $this->pick(['converted', 'closed', 'contacted', 'contacted']),
                        $age > 6 => $this->pick(['contacted', 'contacted', 'new']),
                        default => 'new',
                    };

                    $partners = collect(D::commissionPartners())->filter(fn ($p) => Str::lower($p['city']) === $citySlug && isset($this->users[$p['key']]));

                    $lead = Lead::create([
                        'name' => "{$first} {$last}",
                        'email' => Str::lower($first.'.'.$last.mt_rand(1, 99)).'@'.D::DEMO_DOMAIN,
                        'phone' => '9'.mt_rand(100000000, 999999999),
                        'city' => $this->cities[$citySlug]->name,
                        'message' => $this->pick($messages[$source]),
                        'source' => $source,
                        'interested_plan_id' => in_array($source, ['homepage', 'contact_page', 'chatbot'], true) ? $this->pick($this->plans)->id : null,
                        'vip_microsite_id' => $site?->id,
                        'product_id' => $source === 'product' ? $this->catalogue->random()->id : null,
                        'status' => $status,
                        'assigned_manager_id' => ! $site && $partners->isNotEmpty() && $this->chance(80) ? $this->users[$partners->random()['key']]->id : null,
                    ]);

                    if ($status !== 'new' || $age > 2) {
                        $lead->forceFill(['seen_at' => now()->addHours(mt_rand(1, 30))])->saveQuietly();
                    }

                    $this->counts['leads']++;
                });
            }
        }
    }

    // =================================================================== money

    private function planMoneyCycles(): void
    {
        for ($m = 1; $m < 13; $m++) {
            $month = $this->monthStart($m);
            $period = $month->copy()->subMonth()->format('Y-m');

            // Super Admin settles each Commission Partner's previous month.
            $this->at($month->copy()->addDays(5)->setTime(12, 30), function () use ($period) {
                foreach (D::commissionPartners() as $p) {
                    if (isset($this->users[$p['key']])) {
                        $this->service(PartnerPayoutService::class)->markPaid(
                            $this->users[$p['key']]->fresh(), $period, $this->superAdmin,
                            'NEFT/UTR '.mt_rand(100000, 999999).mt_rand(100000, 999999), 'Monthly commission settlement',
                        );
                    }
                }
            });

            // VIP members withdraw what their storefront earned.
            foreach (D::businesses() as $b) {
                $this->at($month->copy()->addDays(mt_rand(7, 14))->setTime(mt_rand(10, 19), mt_rand(0, 59)), function () use ($b) {
                    $member = ($this->users['vip:'.$b['key']] ?? null)?->fresh();
                    if (! $member) {
                        return;
                    }

                    $withdrawals = $this->service(WithdrawalService::class);
                    $available = $withdrawals->availableBalance($member);
                    if ($available < 1000 || ! $withdrawals->canRequest($member)) {
                        return;
                    }

                    $request = $withdrawals->request($member, floor($available * mt_rand(60, 100) / 100 / 100) * 100);

                    $this->at(now()->addDays(mt_rand(1, 3))->setTime(mt_rand(11, 17), mt_rand(0, 59)), function () use ($request) {
                        $this->service(WithdrawalService::class)->markPaid(
                            WithdrawalRequest::findOrFail($request->id),
                            $this->users['kavita-iyer'] ?? $this->superAdmin,
                            'UPI '.mt_rand(100000, 999999).mt_rand(100000, 999999),
                            null,
                            'Paid to the bank account on file.',
                        );
                    });
                });
            }
        }

        // Businesses selling their own products are paid out twice a month.
        for ($m = 0; $m < 13; $m++) {
            foreach ([1, 16] as $day) {
                $this->at($this->monthStart($m)->addDays($day - 1)->setTime(15, 30), function () {
                    foreach ($this->sites as $key => $site) {
                        if (! isset($this->biz[$key]['own'])) {
                            continue;
                        }
                        $sales = $this->service(BusinessSalesService::class);
                        if ($sales->payableAmount($site->fresh()) > 0) {
                            $sales->settle($site->fresh(), $this->superAdmin, 'IMPS '.mt_rand(100000, 999999).mt_rand(100000, 999999), null, 'Settlement of delivered online orders.');
                        }
                    }
                });
            }
        }
    }
}
