<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\BusinessProduct;
use App\Models\BusinessSection;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Support\BusinessModules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * A fully filled-in demo VIP page: "Lifeline Multi-Speciality Hospital", Jhansi.
 *
 * Every section of the page builder has content — banners, about, highlights,
 * departments (services), a health store (own products with stock and prices),
 * doctors and facilities (owner-built card sections), gallery, FAQs, reviews,
 * contact details and hours — plus three sample orders so the VIP "My Orders"
 * page and the admin Business Sales report have something to show.
 *
 * Images are generated SVG illustrations written to the public disk, so the
 * seeder needs no network and no stock photos. Safe to run again: it rebuilds
 * this one microsite's content and touches nothing else.
 *
 *   php artisan db:seed --class=HospitalMicrositeSeeder
 *   Login: hospital@globallife.in / Password!123
 */
class HospitalMicrositeSeeder extends Seeder
{
    private const DIR = 'uploads/demo/hospital';

    public function run(): void
    {
        $city = City::where('slug', 'jhansi')->first() ?? City::first();
        $plan = VipPlan::where('slug', 'premium')->first() ?? VipPlan::orderByDesc('product_limit')->first();
        $partner = User::where('email', 'jhansi.managerA@globallife.in')->first()
            ?? User::role('commission_partner')->first();

        if (! $city || ! $plan || ! $partner) {
            $this->command?->warn('HospitalMicrositeSeeder needs a city, a VIP plan and a Commission Partner. Run the main DatabaseSeeder first.');

            return;
        }

        DB::transaction(function () use ($city, $plan, $partner) {
            $owner = User::updateOrCreate(
                ['email' => 'hospital@globallife.in'],
                [
                    'name' => 'Dr. Rajesh Agarwal',
                    'mobile' => '9876500001',
                    'password' => Hash::make('Password!123'),
                    'status' => 'active',
                    'created_by' => $partner->id,
                    'email_verified_at' => now(),
                ],
            );
            $owner->syncRoles(['vip_member']);

            $microsite = VipMicrosite::updateOrCreate(['user_id' => $owner->id], [
                'city_id' => $city->id,
                'vip_plan_id' => $plan->id,
                'business_name' => 'Lifeline Multi-Speciality Hospital',
                'business_slug' => 'lifeline-multispeciality-hospital',
                'secure_token' => VipMicrosite::where('user_id', $owner->id)->value('secure_token') ?? strtoupper(Str::random(7)),
                'status' => 'active',
                'activated_at' => now()->subMonths(2),
                'plan_expires_at' => now()->addMonths(10),
                'business_category' => 'Healthcare',
                'business_sub_category' => 'Multi-Speciality Hospital',
                'owner_name' => 'Dr. Rajesh Agarwal',
                'short_description' => 'Bundelkhand’s trusted 250-bed multi-speciality hospital — expert doctors, modern technology and 24×7 emergency care under one roof.',
                'description' => "Since 2006, Lifeline has cared for more than one lakh families across Jhansi and Bundelkhand.\n\nOur 60+ specialists work across 25 departments, backed by a modular operation theatre complex, a cath lab, a 128-slice CT scanner, a level-III NICU and an in-house blood bank. Every patient gets one care coordinator from admission to discharge, transparent package pricing, and cashless treatment with all major insurers and government schemes.",
                'establishment_year' => 2006,
                'gst_no' => '09AABCL1234F1Z5',
                'logo_path' => $this->image('logo.svg', $this->logoSvg()),
                'cover_banner_path' => $this->image('about.svg', $this->sceneSvg('Caring for Bundelkhand since 2006', '🏥', '#0f766e', '#134e4a', 1200, 800)),
                'business_email' => 'care@lifelinehospital.in',
                'phone_number' => '9876500001',
                'alternate_number' => '05102470001',
                'whatsapp_number' => '9876500001',
                'website_url' => null,
                'address' => 'NH-44, Gwalior Road, near Medical College Crossing, Jhansi, Uttar Pradesh 284001',
                'google_map_url' => 'https://maps.google.com/?q=Medical+College+Jhansi',
                'business_hours' => collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
                    ->mapWithKeys(fn ($day) => [$day => ['open' => '00:00', 'close' => '23:59', 'closed' => false]])->all(),
                'facebook_url' => 'https://facebook.com/',
                'instagram_url' => 'https://instagram.com/',
                'youtube_url' => 'https://youtube.com/',
                'module_visibility' => ['videos' => false] + BusinessModules::defaults(),
            ]);

            $this->resetContent($microsite);
            $this->seedBanners($microsite);
            $this->seedServices($microsite);
            $this->seedProducts($microsite);
            [$doctors, $facilities] = $this->seedCustomSections($microsite);
            $this->seedGallery($microsite);
            $this->seedFaqs($microsite);
            $this->seedReviews($microsite);

            $microsite->update(['page_layout' => [
                'order' => [
                    'about', 'stats', 'services', $doctors->layoutKey(), $facilities->layoutKey(),
                    'products', 'shop', 'gallery', 'reviews', 'faqs', 'videos', 'contact', 'social_media',
                ],
                'headings' => [
                    'about' => ['eyebrow' => 'Welcome to Lifeline', 'title' => 'Advanced care, close to home'],
                    'stats' => ['eyebrow' => 'Why families trust us', 'title' => 'Lifeline in numbers'],
                    'services' => ['eyebrow' => 'Centres of Excellence', 'title' => 'Our Specialities', 'subtitle' => 'Consultation fees shown are for a first OPD visit. Packages include room, OT, medicines and doctor visits.'],
                    'products' => ['eyebrow' => 'Lifeline Health Store', 'title' => 'Home-care essentials', 'subtitle' => 'Genuine devices and supplies, delivered by our own pharmacy team across Jhansi.'],
                    'reviews' => ['eyebrow' => 'Patient Stories', 'title' => 'What our patients say'],
                    'faqs' => ['eyebrow' => 'Help Desk', 'title' => 'Before you visit'],
                    'contact' => ['eyebrow' => 'Open 24 × 7', 'title' => 'Reach us any time'],
                ],
                'stats' => [
                    ['label' => 'Beds', 'value' => '250', 'suffix' => '+', 'is_active' => true],
                    ['label' => 'Specialist doctors', 'value' => '60', 'suffix' => '+', 'is_active' => true],
                    ['label' => 'Specialities', 'value' => '25', 'suffix' => '', 'is_active' => true],
                    ['label' => 'Patients treated', 'value' => '1,00,000', 'suffix' => '+', 'is_active' => true],
                ],
            ]]);

            $this->seedOrders($microsite);
        });

        $this->command?->info('Lifeline Multi-Speciality Hospital seeded. Login: hospital@globallife.in / Password!123');
    }

    private function resetContent(VipMicrosite $microsite): void
    {
        $microsite->banners()->delete();
        $microsite->services()->delete();
        $microsite->galleryItems()->delete();
        $microsite->faqs()->delete();
        $microsite->reviews()->delete();
        $microsite->customSections()->each(function (BusinessSection $section) {
            $section->cards()->delete();
            $section->delete();
        });

        // Demo orders reference products; remove them before the products.
        Order::where('fulfilled_by_microsite_id', $microsite->id)->get()->each(function (Order $order) {
            $order->items()->delete();
            $order->delete();
        });
        $microsite->settlements()->delete();
        $microsite->products()->delete();
    }

    private function seedBanners(VipMicrosite $microsite): void
    {
        $banners = [
            ['24×7 Emergency & Trauma Care', 'Ambulance at your door in 20 minutes anywhere in Jhansi. Call 98765 00001.', 'Call Emergency', 'tel:9876500001', '🚑', '#0f766e', '#042f2e'],
            ['Heart care that never sleeps', 'Cath lab, angioplasty and bypass surgery by a team of five cardiologists.', 'Book a Cardiology OPD', '#enquiry', '🫀', '#9f1239', '#4c0519'],
            ['Cashless with 40+ insurers', 'Ayushman Bharat, CGHS, ECHS and all major TPAs accepted.', 'Check eligibility', '#faqs', '🛡️', '#1d4ed8', '#172554'],
        ];

        foreach ($banners as $i => [$heading, $sub, $button, $link, $emoji, $c1, $c2]) {
            $microsite->banners()->create([
                'device' => 'desktop',
                'image_path' => $this->image("banner-$i.svg", $this->bannerSvg($emoji, $c1, $c2)),
                'heading' => $heading,
                'subheading' => $sub,
                'button_text' => $button,
                'button_link' => $link,
                'sort_order' => $i,
                'is_visible' => true,
            ]);
        }
    }

    private function seedServices(VipMicrosite $microsite): void
    {
        $services = [
            ['Cardiology', 'Heart', '🫀', '#be123c', 800, 600, 'Angiography, angioplasty, pacemakers and bypass surgery by a 5-member heart team.', "• 24×7 cath lab with primary angioplasty for heart attacks\n• Echo, TMT, Holter and ECG under one roof\n• Cardiac ICU with 12 beds\n• Heart failure and hypertension clinic every Thursday"],
            ['Neurology & Neurosurgery', 'Brain & Spine', '🧠', '#7c3aed', 900, null, 'Stroke unit, epilepsy care and minimally invasive spine surgery.', "• Thrombolysis-ready stroke unit (golden hour protocol)\n• EEG, EMG and nerve conduction studies\n• Microscopic brain and spine surgery"],
            ['Orthopaedics & Joint Replacement', 'Bones & Joints', '🦴', '#b45309', 700, 650, 'Knee and hip replacement, arthroscopy and fracture care.', "• Robotic-assisted knee replacement\n• Sports injury and arthroscopy clinic\n• Day-care fracture fixation\n• In-house physiotherapy"],
            ['Obstetrics & Gynaecology', 'Women’s Health', '🤰', '#db2777', 600, null, 'Normal and painless delivery, high-risk pregnancy care and laparoscopy.', "• Labour suites with epidural (painless) delivery\n• High-risk pregnancy clinic\n• Laparoscopic hysterectomy and fibroid removal\n• Maternity packages from ₹24,999"],
            ['Paediatrics & NICU', 'Child Care', '👶', '#0284c7', 500, null, 'Level-III NICU, vaccination and newborn care, round the clock.', "• 20-bed level-III NICU with ventilators\n• Vaccination clinic (all IAP vaccines)\n• Paediatric emergency 24×7"],
            ['Nephrology & Dialysis', 'Kidney Care', '🩺', '#0d9488', 700, null, '12-station dialysis unit and kidney transplant work-up.', "• Haemodialysis 6 am – 10 pm, all days\n• Dialysis under Ayushman Bharat\n• Kidney stone and transplant clinic"],
            ['Oncology', 'Cancer Care', '🎗️', '#9333ea', 1000, null, 'Chemotherapy day-care, cancer surgery and tumour board.', "• Weekly multi-disciplinary tumour board\n• Day-care chemotherapy suite\n• Breast, oral and cervical cancer screening"],
            ['Full Body Health Check-up', 'Preventive', '✅', '#16a34a', 3999, 2499, '72 tests, doctor consultation and report review in 4 hours.', "Includes CBC, lipid profile, liver and kidney function, HbA1c, thyroid profile, vitamin D & B12, urine routine, ECG, chest X-ray and ultrasound abdomen.\n\nFasting of 10–12 hours required. Reports on WhatsApp the same day."],
        ];

        foreach ($services as $i => [$name, $category, $emoji, $colour, $mrp, $offer, $short, $long]) {
            $microsite->services()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'category' => $category,
                'short_description' => $short,
                'long_description' => $long,
                'image_path' => $this->image('service-'.Str::slug($name).'.svg', $this->sceneSvg($name, $emoji, $colour, $this->darken($colour), 800, 600)),
                'mrp' => $mrp,
                'offer_price' => $offer,
                'discount_percent' => $offer ? round(($mrp - $offer) / $mrp * 100) : null,
                'show_pricing' => true,
                'show_book_now' => true,
                'status' => 'published',
                'is_featured' => $i < 3,
                'sort_order' => $i,
                'meta_title' => $name,
                'meta_description' => $short,
            ]);
        }
    }

    private function seedProducts(VipMicrosite $microsite): void
    {
        $products = [
            ['Digital BP Monitor', 'Devices', '🩺', 2499, 1799, 25, 'Fully automatic upper-arm monitor with irregular-heartbeat alert.', "Clinically validated, 2 × 60 memory, large backlit display. 5-year warranty. Our nurse will show you how to use it at delivery."],
            ['Glucometer Kit + 50 Strips', 'Diabetes Care', '🩸', 1650, 1199, 40, 'Accurate sugar readings in 5 seconds, with 50 strips and lancets.', "No coding needed. Kit includes meter, 50 test strips, 50 lancets, lancing device and carry pouch."],
            ['Pulse Oximeter', 'Devices', '❤️', 1499, 899, 30, 'Finger-tip SpO₂ and pulse reading with OLED display.', 'Shows oxygen saturation, pulse rate and perfusion index. Includes 2 AAA batteries and lanyard.'],
            ['Compressor Nebulizer', 'Respiratory', '💨', 2799, 1990, 12, 'For asthma and chest congestion — for children and adults.', 'Low-noise piston compressor with adult and child masks, mouthpiece and 5 spare filters.'],
            ['Knee Support Brace (Pair)', 'Ortho Care', '🦵', 999, 749, 0, 'Breathable compression brace for arthritis and sports injuries.', 'Available in S, M, L and XL. Mention your size in the delivery notes.'],
            ['Infrared Thermometer', 'Devices', '🌡️', 1299, 849, 50, 'Non-contact forehead thermometer, reading in 1 second.', 'Fever alarm, 32-reading memory, silent mode for sleeping children.'],
            ['Family First-Aid Kit', 'First Aid', '🩹', 899, 649, 60, '45-piece kit for home, car and travel.', 'Bandages, gauze, antiseptic, cold pack, scissors, tweezers and an illustrated first-aid guide by our emergency team.'],
            ['N95 Masks (Pack of 10)', 'Protection', '😷', 600, 399, 100, 'BIS-certified 5-layer masks with adjustable nose clip.', 'Recommended for patients with low immunity, during pollution season and while visiting hospitals.'],
        ];

        foreach ($products as $i => [$name, $category, $emoji, $mrp, $offer, $stock, $short, $long]) {
            BusinessProduct::create([
                'vip_microsite_id' => $microsite->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'category' => $category,
                'short_description' => $short,
                'long_description' => $long,
                'image_path' => $this->image('product-'.Str::slug($name).'.svg', $this->productSvg($emoji, $i)),
                'mrp' => $mrp,
                'offer_price' => $offer,
                'discount_percent' => round(($mrp - $offer) / $mrp * 100),
                'show_pricing' => true,
                'sku' => 'LL-'.str_pad((string) ($i + 101), 4, '0', STR_PAD_LEFT),
                'stock' => $stock,
                'brand' => 'Lifeline Health Store',
                'status' => 'published',
                'is_featured' => $i < 2,
                'sort_order' => $i,
                'meta_title' => $name,
                'meta_description' => $short,
            ]);
        }
    }

    /**
     * @return array{0: BusinessSection, 1: BusinessSection}
     */
    private function seedCustomSections(VipMicrosite $microsite): array
    {
        $doctors = $microsite->customSections()->create([
            'eyebrow' => 'Our Specialists',
            'title' => 'Meet Our Doctors',
            'subtitle' => 'Senior consultants with training from AIIMS, PGI Chandigarh and KGMU.',
            'style' => 'people',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $people = [
            ['Dr. Rajesh Agarwal', 'MS, MCh – Cardiothoracic Surgery', 'Founder & Medical Director. 3,000+ heart surgeries.', "28 years of experience. Trained at AIIMS New Delhi. Pioneered beating-heart bypass surgery in Bundelkhand.\n\nOPD: Mon, Wed, Fri · 10 am – 2 pm", '#0f766e'],
            ['Dr. Anjali Mehra', 'DM – Cardiology', 'Interventional cardiologist, 1,500+ angioplasties.', "Former senior resident, PGI Chandigarh. Special interest in women’s heart health.\n\nOPD: Mon – Sat · 9 am – 1 pm", '#be123c'],
            ['Dr. Vikram Singh', 'MCh – Neurosurgery', 'Brain tumour and minimally invasive spine surgery.', "15 years of experience. Fellowship in endoscopic spine surgery, Germany.\n\nOPD: Tue, Thu, Sat · 11 am – 3 pm", '#7c3aed'],
            ['Dr. Priya Sharma', 'MS, DNB – Obstetrics & Gynaecology', 'High-risk pregnancy and laparoscopic surgery.', "Delivered 6,000+ babies. Leads our painless-delivery programme.\n\nOPD: Mon – Sat · 10 am – 4 pm", '#db2777'],
            ['Dr. Sameer Khan', 'MS – Orthopaedics, Fellowship Arthroplasty', 'Knee and hip replacement surgeon.', "1,200+ joint replacements. Sports medicine consultant for the district cricket association.\n\nOPD: Mon – Fri · 5 pm – 8 pm", '#b45309'],
            ['Dr. Neha Gupta', 'MD – Paediatrics, Fellowship Neonatology', 'Heads the level-III NICU.', "Specialist in premature and critically ill newborns.\n\nOPD: Mon – Sat · 9 am – 12 pm", '#0284c7'],
            ['Dr. Arvind Tiwari', 'DM – Nephrology', 'Dialysis and kidney transplant work-up.', "Started Jhansi’s first 12-station dialysis unit.\n\nOPD: Tue, Thu, Sat · 10 am – 1 pm", '#0d9488'],
            ['Dr. Kavita Rao', 'MD, DM – Medical Oncology', 'Chemotherapy and targeted cancer therapy.', "Leads the weekly tumour board. Former consultant, Tata Memorial Centre.\n\nOPD: Wed, Fri · 11 am – 3 pm", '#9333ea'],
        ];

        foreach ($people as $i => [$name, $role, $short, $long, $colour]) {
            $doctors->cards()->create([
                'image_path' => $this->image('doctor-'.Str::slug($name).'.svg', $this->avatarSvg($name, $colour)),
                'title' => $name,
                'subtitle' => $role,
                'short_description' => $short,
                'long_description' => $long,
                'button_label' => 'Book OPD',
                'button_url' => 'tel:9876500001',
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        $facilities = $microsite->customSections()->create([
            'eyebrow' => 'Infrastructure',
            'title' => 'World-class Facilities',
            'subtitle' => 'Technology you would otherwise travel to Delhi or Lucknow for.',
            'style' => 'cards',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $items = [
            ['Modular Operation Theatres', '6 OTs · HEPA filtered', '🔬', '#0f766e', 'Six laminar-flow modular OTs with HEPA filtration and integrated HD imaging.'],
            ['Cath Lab', '24 × 7', '🫀', '#be123c', 'Flat-panel cath lab for angiography and angioplasty, with a team on call round the clock.'],
            ['128-Slice CT & 1.5T MRI', 'Imaging', '🩻', '#1d4ed8', 'Low-dose CT and silent MRI with reports in 2 hours.'],
            ['Level-III NICU', '20 beds', '👶', '#0284c7', 'Ventilators, phototherapy and a neonatologist in-house all night.'],
            ['Blood Bank', 'Licensed', '🩸', '#b91c1c', 'Component separation and 24×7 availability of all blood groups.'],
            ['24 × 7 Pharmacy', 'In-house', '💊', '#16a34a', 'Genuine medicines at MRP or less, with home delivery across Jhansi.'],
        ];

        foreach ($items as $i => [$title, $subtitle, $emoji, $colour, $short]) {
            $facilities->cards()->create([
                'image_path' => $this->image('facility-'.Str::slug($title).'.svg', $this->sceneSvg($title, $emoji, $colour, $this->darken($colour), 800, 600)),
                'title' => $title,
                'subtitle' => $subtitle,
                'short_description' => $short,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        return [$doctors, $facilities];
    }

    private function seedGallery(VipMicrosite $microsite): void
    {
        $shots = [
            ['Reception & help desk', '🛎️', '#0f766e'], ['Private suite', '🛏️', '#1d4ed8'], ['ICU', '🫁', '#be123c'],
            ['Pharmacy', '💊', '#16a34a'], ['Physiotherapy', '🏃', '#b45309'], ['Ambulance fleet', '🚑', '#b91c1c'],
            ['Diagnostics', '🧪', '#7c3aed'], ['Cafeteria', '☕', '#a16207'],
        ];

        foreach ($shots as $i => [$title, $emoji, $colour]) {
            $microsite->galleryItems()->create([
                'image_path' => $this->image('gallery-'.$i.'.svg', $this->sceneSvg($title, $emoji, $colour, $this->darken($colour), 800, $i % 3 === 0 ? 1000 : 600)),
                'title' => $title,
                'sort_order' => $i,
                'is_visible' => true,
            ]);
        }
    }

    private function seedFaqs(VipMicrosite $microsite): void
    {
        $faqs = [
            ['Is the emergency open at night?', 'Yes. Our emergency, ICU, pharmacy, lab and ambulance run 24 hours a day, 365 days a year. Call 98765 00001.'],
            ['Do you accept Ayushman Bharat and health insurance?', 'Yes. We are empanelled under Ayushman Bharat (PM-JAY), CGHS and ECHS, and offer cashless treatment with 40+ insurers and TPAs. Bring your card and a photo ID.'],
            ['How do I book a doctor’s appointment?', 'Call or WhatsApp 98765 00001, or use the enquiry form on this page. Walk-ins are welcome; booked patients are seen first.'],
            ['What should I bring for admission?', 'Photo ID, insurance card or policy copy, previous reports and the medicines you currently take.'],
            ['Are visiting hours fixed?', 'Wards: 11 am – 1 pm and 5 pm – 7 pm. ICU: one attendant at a time, 5 pm – 6 pm.'],
            ['Do you deliver medicines and devices at home?', 'Yes. Order from our Health Store on this page; our pharmacy team delivers across Jhansi, usually the same day.'],
        ];

        foreach ($faqs as $i => [$question, $answer]) {
            $microsite->faqs()->create(['question' => $question, 'answer' => $answer, 'sort_order' => $i, 'is_visible' => true]);
        }
    }

    private function seedReviews(VipMicrosite $microsite): void
    {
        $reviews = [
            ['Ramesh Yadav', 5, 'My father had a heart attack at 2 am. The cath lab team did an angioplasty within an hour. We are forever grateful to Dr. Mehra and the ICU nurses.'],
            ['Sunita Verma', 5, 'Painless delivery with Dr. Priya Sharma was a wonderful experience. Clean rooms and very caring staff.'],
            ['Mohd. Irfan', 4, 'Knee replacement done under Ayushman Bharat without paying anything. Physiotherapy team is excellent.'],
            ['Pooja Saxena', 5, 'The NICU saved our premature baby. Dr. Neha explained everything patiently every single day.'],
            ['Alok Mishra', 4, 'Health check-up was quick and organised, reports came on WhatsApp the same evening.'],
            ['Geeta Kushwaha', 5, 'Dialysis staff are like family now. Clean unit and always on time.'],
        ];

        foreach ($reviews as $i => [$name, $rating, $text]) {
            $microsite->reviews()->create([
                'customer_name' => $name,
                'rating' => $rating,
                'review_text' => $text,
                'review_date' => now()->subDays(5 + $i * 9),
                'is_verified' => true,
                'is_featured' => $i < 3,
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Three sample orders for the hospital's own products, one in each state the
     * reports care about: COD delivered, online delivered (owed), online open.
     */
    private function seedOrders(VipMicrosite $microsite): void
    {
        Role::findOrCreate('customer', 'web');

        $customer = User::updateOrCreate(['email' => 'patient.demo@globallife.in'], [
            'name' => 'Amit Patel',
            'mobile' => '9876511111',
            'password' => Hash::make('Password!123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $customer->syncRoles(['customer']);

        Address::updateOrCreate(['user_id' => $customer->id, 'pincode' => '284003'], [
            'name' => 'Amit Patel',
            'phone' => '9876511111',
            'address' => '14, Civil Lines, near Elite Chauraha',
            'city' => 'Jhansi',
            'state' => 'Uttar Pradesh',
            'is_default' => true,
        ]);

        $products = $microsite->products()->get()->keyBy('slug');

        $orders = [
            ['cod', 'delivered', 8, [['digital-bp-monitor', 1], ['glucometer-kit-50-strips', 1]]],
            ['online', 'delivered', 4, [['compressor-nebulizer', 1], ['pulse-oximeter', 2]]],
            ['online', 'confirmed', 0, [['family-first-aid-kit', 1], ['infrared-thermometer', 1]]],
        ];

        foreach ($orders as $n => [$method, $status, $daysAgo, $lines]) {
            $placed = now()->subDays($daysAgo)->setTime(10 + $n, 15);
            $subtotal = 0;
            $rows = [];
            foreach ($lines as [$slug, $qty]) {
                $product = $products->get($slug);
                if (! $product) {
                    continue;
                }
                $lineTotal = round((float) $product->sellingPrice() * $qty, 2);
                $subtotal += $lineTotal;
                $rows[] = [$product, $qty, $lineTotal];
            }

            $order = Order::create([
                'order_number' => 'GLDEMO'.($n + 1).strtoupper(Str::random(4)),
                'user_id' => $customer->id,
                'fulfilled_by_microsite_id' => $microsite->id,
                'customer_name' => 'Amit Patel',
                'customer_email' => $customer->email,
                'customer_phone' => '9876511111',
                'address' => '14, Civil Lines, near Elite Chauraha',
                'city' => 'Jhansi',
                'state' => 'Uttar Pradesh',
                'pincode' => '284003',
                'payment_method' => $method,
                'payment_gateway' => $method === 'online' ? 'razorpay' : null,
                'razorpay_payment_id' => $method === 'online' ? 'pay_demo'.Str::random(10) : null,
                'payment_status' => $method === 'online' || $status === 'delivered' ? 'paid' : 'pending',
                'status' => $status,
                'subtotal' => $subtotal,
                'shipping' => 0,
                'total' => $subtotal,
                'commission_credited' => $status === 'delivered',
                'placed_at' => $placed,
                'expected_delivery_date' => $placed->copy()->addDays(2)->toDateString(),
                'processing_at' => $status === 'delivered' ? $placed->copy()->addHours(2) : null,
                'dispatched_at' => $status === 'delivered' ? $placed->copy()->addHours(20) : null,
                'delivered_at' => $status === 'delivered' ? $placed->copy()->addDay() : null,
            ]);

            foreach ($rows as [$product, $qty, $lineTotal]) {
                $order->items()->create([
                    'product_id' => null,
                    'business_product_id' => $product->id,
                    'seller_microsite_id' => $microsite->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->sellingPrice(),
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------ images

    private function image(string $name, string $svg): string
    {
        $path = self::DIR.'/'.$name;
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function darken(string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', (int) ($r * 0.45), (int) ($g * 0.45), (int) ($b * 0.45));
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function bannerSvg(string $emoji, string $c1, string $c2): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 700" width="1600" height="700">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$c2}"/><stop offset="1" stop-color="{$c1}"/></linearGradient>
    <pattern id="p" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M26 18h8v8h8v8h-8v8h-8v-8h-8v-8h8z" fill="#fff" opacity=".05"/></pattern>
  </defs>
  <rect width="1600" height="700" fill="url(#g)"/>
  <rect width="1600" height="700" fill="url(#p)"/>
  <circle cx="1250" cy="350" r="300" fill="#fff" opacity=".08"/>
  <circle cx="1250" cy="350" r="210" fill="#fff" opacity=".1"/>
  <text x="1250" y="420" font-size="200" text-anchor="middle" font-family="Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, sans-serif">{$emoji}</text>
  <path d="M0 610 C 400 560 800 680 1600 600 L1600 700 L0 700 Z" fill="#fff" opacity=".08"/>
</svg>
SVG;
    }

    private function sceneSvg(string $title, string $emoji, string $c1, string $c2, int $w, int $h): string
    {
        $title = $this->esc($title);
        $cx = $w / 2;
        $cy = $h / 2 - 20;
        $r = min($w, $h) * 0.28;
        $font = (int) ($r * 1.1);
        $ty = $cy + $font * 0.36;
        $labelY = $h - 48;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$c1}"/><stop offset="1" stop-color="{$c2}"/></linearGradient></defs>
  <rect width="{$w}" height="{$h}" fill="url(#g)"/>
  <circle cx="{$w}" cy="0" r="{$r}" fill="#fff" opacity=".07"/>
  <circle cx="0" cy="{$h}" r="{$r}" fill="#fff" opacity=".07"/>
  <circle cx="{$cx}" cy="{$cy}" r="{$r}" fill="#fff" opacity=".14"/>
  <text x="{$cx}" y="{$ty}" font-size="{$font}" text-anchor="middle" font-family="Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, sans-serif">{$emoji}</text>
  <text x="{$cx}" y="{$labelY}" font-size="34" font-weight="700" fill="#fff" opacity=".92" text-anchor="middle" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif">{$title}</text>
</svg>
SVG;
    }

    private function productSvg(string $emoji, int $i): string
    {
        $tints = ['#ecfdf5', '#eff6ff', '#fdf2f8', '#fefce8', '#f5f3ff', '#fff7ed', '#f0fdfa', '#f1f5f9'];
        $bg = $tints[$i % count($tints)];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800">
  <rect width="800" height="800" fill="{$bg}"/>
  <ellipse cx="400" cy="640" rx="230" ry="34" fill="#0f172a" opacity=".08"/>
  <circle cx="400" cy="380" r="250" fill="#fff"/>
  <text x="400" y="470" font-size="260" text-anchor="middle" font-family="Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, sans-serif">{$emoji}</text>
</svg>
SVG;
    }

    private function avatarSvg(string $name, string $colour): string
    {
        $parts = preg_split('/\s+/', trim(str_replace(['Dr.', 'Mohd.'], '', $name)));
        $initials = $this->esc(strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr(end($parts) ?: '', 0, 1)));
        $dark = $this->darken($colour);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$colour}"/><stop offset="1" stop-color="{$dark}"/></linearGradient></defs>
  <rect width="600" height="600" fill="url(#g)"/>
  <circle cx="300" cy="235" r="105" fill="#fff" opacity=".9"/>
  <path d="M110 600c0-120 85-200 190-200s190 80 190 200z" fill="#fff" opacity=".9"/>
  <path d="M245 420l55 70 55-70" fill="none" stroke="{$colour}" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
  <text x="300" y="262" font-size="82" font-weight="800" fill="{$dark}" text-anchor="middle" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif">{$initials}</text>
</svg>
SVG;
    }

    private function logoSvg(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#14b8a6"/><stop offset="1" stop-color="#0f766e"/></linearGradient></defs>
  <rect width="400" height="400" rx="90" fill="url(#g)"/>
  <path d="M165 90h70v75h75v70h-75v75h-70v-75H90v-70h75z" fill="#fff"/>
  <path d="M100 205h55l20-40 30 80 20-40h75" fill="none" stroke="#0f766e" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
    }
}
