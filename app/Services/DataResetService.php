<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

/**
 * "Erase all data": wipes every piece of business data so the platform starts
 * fresh, while keeping what the site needs to run.
 *
 * Every table is classified below. A table in no list is LEFT ALONE, and
 * DataResetTest::test_every_table_is_classified fails until a new table is
 * added to one, so a future module is never silently skipped or wiped.
 *
 * Safety:
 *  - a full SQL backup is written first (storage/app/private/backups/…);
 *  - all deletes run in one transaction: any failure rolls everything back;
 *  - uploaded files are not deleted but MOVED into the same backup folder,
 *    and only when nothing that is kept still points at them.
 */
class DataResetService
{
    /** Business data: emptied completely. */
    public const WIPE = [
        // people & money
        'addresses', 'wallets', 'withdrawal_requests', 'orders', 'order_items', 'pending_orders',
        'commission_earnings', 'commission_transactions', 'commission_payouts', 'commission_partner_updates',
        'user_profiles', 'branch_manager_cities', 'city_manager',
        // VIP members' business pages
        'vip_microsites', 'vip_products', 'vip_renewals', 'business_banners', 'business_faqs',
        'business_gallery_items', 'business_products', 'business_profile_events', 'business_reviews',
        'business_sections', 'business_section_cards', 'business_services', 'business_settlements', 'business_videos',
        // enquiries & visitor activity
        'leads', 'blog_comments', 'blog_likes',
        // HR records
        'employees', 'employee_documents', 'employee_salaries', 'attendances', 'attendance_regularizations',
        'leave_balances', 'leave_requests', 'payroll_runs', 'payslips', 'salary_advances', 'assets',
        'asset_assignments', 'expense_claims', 'announcements',
        // system history / queues
        'activity_log', 'password_reset_tokens', 'jobs', 'job_batches', 'failed_jobs', 'cache', 'cache_locks',
    ];

    /** Emptied except for what belongs to the Super Admins / the current login. */
    public const PARTIAL = ['users', 'model_has_roles', 'model_has_permissions', 'sessions'];

    /** Site setup and catalogue: never touched. */
    public const KEEP = [
        'migrations', 'roles', 'permissions', 'role_has_permissions', 'settings',
        'products', 'product_benefits', 'categories', 'brands', 'cities', 'vip_plans', 'commission_rules',
        'home_sections', 'blog_posts', 'events', 'testimonials', 'media_items',
        'departments', 'designations', 'holidays', 'shifts', 'leave_types',
    ];

    public const BACKUP_DISK = 'local';

    public const BACKUP_DIR = 'backups';

    /**
     * Disks and folders whose files may belong to wiped data. The public disk
     * also holds product photos and the logo, so only unreferenced files move.
     *
     * @var array<string, string>
     */
    private const FILE_AREAS = ['public' => 'uploads', 'kyc' => ''];

    public function isEnabled(): bool
    {
        return strlen((string) config('data_reset.password')) >= 8;
    }

    public function passwordMatches(string $given): bool
    {
        return $this->isEnabled() && hash_equals((string) config('data_reset.password'), $given);
    }

    /**
     * What an erase would remove right now, for the confirmation screen.
     *
     * @return array<string, int>
     */
    public function summary(): array
    {
        $count = fn (string $table) => Schema::hasTable($table) ? DB::table($table)->count() : 0;

        return [
            'Users (everyone except Super Admins)' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))->count(),
            'Orders' => $count('orders'),
            'Leads / enquiries' => $count('leads'),
            'VIP business pages' => $count('vip_microsites'),
            'Wallets & commission records' => $count('wallets') + $count('commission_earnings') + $count('commission_transactions'),
            'Withdrawal requests' => $count('withdrawal_requests'),
            'Employees (HR)' => $count('employees'),
            'Attendance & leave records' => $count('attendances') + $count('leave_requests'),
            'Payslips' => $count('payslips'),
            'Blog comments & likes' => $count('blog_comments') + $count('blog_likes'),
        ];
    }

    /**
     * Back up, then erase. Returns a short report.
     *
     * @return array{backup: string, rows: int, files_moved: int}
     */
    public function eraseAll(User $actor, ?string $keepSessionId = null): array
    {
        @set_time_limit(0);

        $backup = $this->backup();

        $rows = $this->wipeTables($actor, $keepSessionId);
        $filesMoved = $this->moveOrphanFiles($backup);

        Cache::flush();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app(SettingsService::class)->set('data_reset_last', json_encode([
            'at' => now()->toIso8601String(),
            'by' => $actor->name,
            'backup' => $backup,
        ]));

        activity()->causedBy($actor)->log('Erased all business data (backup '.$backup.')');
        Log::warning('All business data erased', ['by' => $actor->id, 'backup' => $backup, 'rows' => $rows, 'files_moved' => $filesMoved]);

        return ['backup' => $backup, 'rows' => $rows, 'files_moved' => $filesMoved];
    }

    /**
     * Full SQL dump of every table, written before anything is touched.
     * Returns the backup folder name (e.g. reset-20261122-193045).
     */
    public function backup(): string
    {
        $name = 'reset-'.now()->format('Ymd-His');
        $disk = Storage::disk(self::BACKUP_DISK);
        $dir = self::BACKUP_DIR.'/'.$name;
        $disk->makeDirectory($dir);

        $path = $disk->path($dir.'/database.sql');
        $out = fopen($path, 'w');

        if ($out === false) {
            throw new RuntimeException('Could not create the backup file; nothing was erased.');
        }

        try {
            $pdo = DB::connection()->getPdo();
            fwrite($out, "-- Global Life backup before \"erase all data\", ".now()->toDateTimeString()."\n");
            fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

            foreach ($this->allTables() as $table) {
                $create = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
                fwrite($out, "DROP TABLE IF EXISTS `{$table}`;\n".end($create).";\n\n");

                foreach (DB::table($table)->cursor() as $row) {
                    $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $row);
                    $columns = implode('`, `', array_keys((array) $row));
                    fwrite($out, "INSERT INTO `{$table}` (`{$columns}`) VALUES (".implode(', ', $values).");\n");
                }

                fwrite($out, "\n");
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (Throwable $e) {
            fclose($out);
            $disk->deleteDirectory($dir);

            throw new RuntimeException('The backup failed, so nothing was erased: '.$e->getMessage(), 0, $e);
        }

        fclose($out);

        return $name;
    }

    /**
     * @return list<array{name: string, size: int, files: int, at: int}>
     */
    public function backups(): array
    {
        $disk = Storage::disk(self::BACKUP_DISK);

        return collect($disk->directories(self::BACKUP_DIR))
            ->map(fn (string $dir) => [
                'name' => basename($dir),
                'size' => $disk->exists($dir.'/database.sql') ? $disk->size($dir.'/database.sql') : 0,
                'files' => count($disk->allFiles($dir.'/files')),
                'at' => $disk->exists($dir.'/database.sql') ? $disk->lastModified($dir.'/database.sql') : 0,
            ])
            ->sortByDesc('at')
            ->values()
            ->all();
    }

    public function backupPath(string $name): ?string
    {
        if (! preg_match('/^reset-\d{8}-\d{6}$/', $name)) {
            return null;
        }

        $file = self::BACKUP_DIR.'/'.$name.'/database.sql';

        return Storage::disk(self::BACKUP_DISK)->exists($file) ? Storage::disk(self::BACKUP_DISK)->path($file) : null;
    }

    public function deleteBackup(string $name): bool
    {
        if (! preg_match('/^reset-\d{8}-\d{6}$/', $name)) {
            return false;
        }

        return Storage::disk(self::BACKUP_DISK)->deleteDirectory(self::BACKUP_DIR.'/'.$name);
    }

    /** @return list<string> */
    public function allTables(): array
    {
        return array_map(
            fn ($row) => (string) array_values((array) $row)[0],
            DB::select('SHOW TABLES'),
        );
    }

    private function wipeTables(User $actor, ?string $keepSessionId): int
    {
        $keepUserIds = User::role('super_admin')->pluck('id')->push($actor->id)->unique()->all();
        $tables = array_values(array_intersect(self::WIPE, $this->allTables()));
        $deleted = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::transaction(function () use ($tables, $keepUserIds, $actor, $keepSessionId, &$deleted) {
                foreach ($tables as $table) {
                    $deleted += DB::table($table)->delete();
                }

                $deleted += DB::table('users')->whereNotIn('id', $keepUserIds)->delete();
                // A kept Super Admin may have been "created by" someone now gone.
                DB::table('users')->whereIn('id', $keepUserIds)->whereNotIn('created_by', $keepUserIds)->update(['created_by' => null]);

                foreach (['model_has_roles', 'model_has_permissions'] as $pivot) {
                    $deleted += DB::table($pivot)
                        ->where(fn ($q) => $q->where('model_type', '!=', User::class)->orWhereNotIn('model_id', $keepUserIds))
                        ->delete();
                }

                $deleted += DB::table('sessions')
                    ->when($keepSessionId, fn ($q) => $q->where('id', '!=', $keepSessionId))
                    ->delete();

                // Blog posts stay; their author may have been a deleted admin.
                DB::table('blog_posts')->whereNotIn('author_id', $keepUserIds)->update(['author_id' => $actor->id]);
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Fresh numbering (order #1, lead #1 …). ALTER TABLE commits implicitly,
        // so it only runs outside a surrounding transaction (never in tests).
        if (DB::transactionLevel() === 0) {
            foreach ($tables as $table) {
                try {
                    DB::statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = 1');
                } catch (Throwable) {
                    // A table without an auto-increment column: nothing to reset.
                }
            }
        }

        return $deleted;
    }

    /**
     * Move uploaded files that nothing kept refers to any more (VIP page
     * photos, KYC scans, HR documents …) into the backup folder. A file is
     * kept when its file name still appears anywhere in the remaining data, so
     * product photos, the logo and homepage images are never touched.
     */
    private function moveOrphanFiles(string $backup): int
    {
        $haystack = $this->referencedText();
        $backupDisk = Storage::disk(self::BACKUP_DISK);
        $moved = 0;

        foreach (self::FILE_AREAS as $diskName => $folder) {
            $disk = Storage::disk($diskName);

            foreach ($disk->allFiles($folder) as $file) {
                // The backups live on the private disk in some setups: never sweep them.
                if (str_starts_with($file, self::BACKUP_DIR.'/') || str_starts_with(basename($file), '.')) {
                    continue;
                }

                if (str_contains($haystack, basename($file))) {
                    continue;
                }

                $target = self::BACKUP_DIR.'/'.$backup.'/files/'.$diskName.'/'.$file;
                $stream = $disk->readStream($file);

                if ($stream && $backupDisk->writeStream($target, $stream)) {
                    is_resource($stream) && fclose($stream);
                    $disk->delete($file);
                    $moved++;
                }
            }
        }

        return $moved;
    }

    /** Every text value left in the kept tables, to look file names up in. */
    private function referencedText(): string
    {
        $text = '';

        foreach (array_merge(self::KEEP, ['users']) as $table) {
            if (! Schema::hasTable($table) || in_array($table, ['migrations', 'permissions', 'roles', 'role_has_permissions'], true)) {
                continue;
            }

            foreach (DB::table($table)->cursor() as $row) {
                foreach ((array) $row as $value) {
                    if (is_string($value) && $value !== '') {
                        $text .= ' '.$value;
                    }
                }
            }
        }

        return $text;
    }
}
