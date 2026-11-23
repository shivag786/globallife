<?php

namespace Database\Seeders\DemoYear;

/**
 * Fixed content for DemoYearSeeder: the people, businesses and products of a
 * believable first year of trading. Every person key matches a photo in
 * storage/app/public/uploads/demo/year/people/{key}.jpg and every business key
 * a folder of photos in storage/app/public/uploads/demo/year/{key}/.
 *
 * "month" values are months after the demo year starts (0 = the first month).
 */
final class DemoYearData
{
    /** Staff and network accounts use the company domain. */
    public const STAFF_DOMAIN = 'globallife.co.in';

    /** VIP businesses and shoppers use a demo sub-domain, so they are easy to find and remove. */
    public const DEMO_DOMAIN = 'demo.globallife.co.in';

    public const PASSWORD = 'Password!123';

    /** @return list<array<string, mixed>> */
    public static function admins(): array
    {
        return [
            ['key' => 'rohit-malhotra', 'name' => 'Rohit Malhotra', 'mobile' => '9810045521', 'gender' => 'male', 'dob' => '1986-04-12', 'city' => 'Delhi', 'state' => 'Delhi', 'pincode' => '110017', 'address' => 'C-41, Saket'],
            ['key' => 'kavita-iyer', 'name' => 'Kavita Iyer', 'mobile' => '9820367714', 'gender' => 'female', 'dob' => '1984-09-23', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400053', 'address' => '12, Lokhandwala Complex, Andheri West'],
        ];
    }

    /** Sub Admins with the permission-matrix modules they look after. */
    public static function subAdmins(): array
    {
        return [
            ['key' => 'ankit-verma', 'name' => 'Ankit Verma', 'mobile' => '9839012245', 'gender' => 'male', 'dob' => '1992-02-18', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'pincode' => '226010', 'address' => '5/120, Vipul Khand, Gomti Nagar', 'modules' => ['blog', 'events', 'testimonials', 'media'], 'month' => 0],
            ['key' => 'sneha-kulkarni', 'name' => 'Sneha Kulkarni', 'mobile' => '9881204417', 'gender' => 'female', 'dob' => '1990-07-05', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411038', 'address' => 'Flat 304, Kumar Pacific, Kothrud', 'modules' => ['branch-managers', 'leads'], 'month' => 0],
            ['key' => 'farhan-qureshi', 'name' => 'Farhan Qureshi', 'mobile' => '9700451236', 'gender' => 'male', 'dob' => '1994-11-30', 'city' => 'Hyderabad', 'state' => 'Telangana', 'pincode' => '500034', 'address' => '8-2-293, Road No. 12, Banjara Hills', 'modules' => ['products', 'leads'], 'month' => 3],
        ];
    }

    /** Branch Managers: commission pool %, the cities of their branch. */
    public static function branchManagers(): array
    {
        return [
            ['key' => 'vikas-chaturvedi', 'name' => 'Vikas Chaturvedi', 'mobile' => '9415078823', 'gender' => 'male', 'dob' => '1982-01-26', 'percent' => 35, 'cities' => ['lucknow', 'jhansi'], 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'pincode' => '226016', 'address' => 'B-14, Sector 21, Indira Nagar', 'owner' => 'sneha-kulkarni'],
            ['key' => 'meera-joshi', 'name' => 'Meera Joshi', 'mobile' => '9822056314', 'gender' => 'female', 'dob' => '1980-06-14', 'percent' => 35, 'cities' => ['pune', 'mumbai'], 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411004', 'address' => '21, Prabhat Road, Deccan Gymkhana', 'owner' => 'sneha-kulkarni'],
            ['key' => 'arjun-reddy', 'name' => 'Arjun Reddy', 'mobile' => '9849332107', 'gender' => 'male', 'dob' => '1985-10-03', 'percent' => 32, 'cities' => ['hyderabad', 'bengaluru'], 'city' => 'Hyderabad', 'state' => 'Telangana', 'pincode' => '500081', 'address' => 'Plot 47, Kavuri Hills, Madhapur', 'owner' => 'sneha-kulkarni'],
        ];
    }

    public static function commissionPartners(): array
    {
        return [
            ['key' => 'saurabh-tiwari', 'name' => 'Saurabh Tiwari', 'mobile' => '9450123387', 'gender' => 'male', 'dob' => '1991-03-09', 'percent' => 20, 'bm' => 'vikas-chaturvedi', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'pincode' => '226001', 'address' => '33, Hazratganj', 'month' => 0],
            ['key' => 'pooja-srivastava', 'name' => 'Pooja Srivastava', 'mobile' => '9839214470', 'gender' => 'female', 'dob' => '1993-08-21', 'percent' => 18, 'bm' => 'vikas-chaturvedi', 'city' => 'Jhansi', 'state' => 'Uttar Pradesh', 'pincode' => '284001', 'address' => '112, Civil Lines', 'month' => 0],
            ['key' => 'imran-siddiqui', 'name' => 'Imran Siddiqui', 'mobile' => '9918034456', 'gender' => 'male', 'dob' => '1989-12-11', 'percent' => 15, 'bm' => 'vikas-chaturvedi', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'pincode' => '226003', 'address' => '7, Nakhas Chowk, Chowk', 'month' => 4],
            ['key' => 'nikhil-deshmukh', 'name' => 'Nikhil Deshmukh', 'mobile' => '9850112093', 'gender' => 'male', 'dob' => '1990-05-17', 'percent' => 20, 'bm' => 'meera-joshi', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411045', 'address' => 'B-702, Baner Road, Baner', 'month' => 0],
            ['key' => 'snehal-patil', 'name' => 'Snehal Patil', 'mobile' => '9769021158', 'gender' => 'female', 'dob' => '1994-01-29', 'percent' => 18, 'bm' => 'meera-joshi', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400064', 'address' => '17, S.V. Road, Malad West', 'month' => 1],
            ['key' => 'rahul-shinde', 'name' => 'Rahul Shinde', 'mobile' => '9930447782', 'gender' => 'male', 'dob' => '1995-09-02', 'percent' => 15, 'bm' => 'meera-joshi', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400072', 'address' => '402, Hiranandani Gardens, Powai', 'month' => 5],
            ['key' => 'lakshmi-narayanan', 'name' => 'Lakshmi Narayanan', 'mobile' => '9845076631', 'gender' => 'female', 'dob' => '1988-11-07', 'percent' => 20, 'bm' => 'arjun-reddy', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'pincode' => '560038', 'address' => '2nd Cross, 100 Feet Road, Indiranagar', 'month' => 0],
            ['key' => 'karthik-rao', 'name' => 'Karthik Rao', 'mobile' => '9866219945', 'gender' => 'male', 'dob' => '1992-04-25', 'percent' => 18, 'bm' => 'arjun-reddy', 'city' => 'Hyderabad', 'state' => 'Telangana', 'pincode' => '500032', 'address' => 'Flat 5B, Gachibowli Main Road', 'month' => 1],
        ];
    }

    /**
     * VIP businesses. `plan` is the joining package; `renewals` lists the
     * package chosen at each renewal (an empty list lets the plan lapse,
     * 'reject' records a rejected renewal). `own` = the business's own
     * products, sold through the company checkout (Premium / direct plan only).
     */
    public static function businesses(): array
    {
        return [
            [
                'key' => 'restaurant', 'month' => 0, 'city' => 'lucknow', 'partner' => 'saurabh-tiwari', 'plan' => 'growth-plus', 'renewals' => [],
                'name' => 'Spice Route Family Restaurant', 'category' => 'Restaurants & Food', 'sub' => 'North Indian & Mughlai Restaurant', 'year' => 2012,
                'owner' => ['key' => 'sanjay-kapoor', 'name' => 'Sanjay Kapoor', 'gender' => 'male', 'mobile' => '9415502231'],
                'short' => 'Lucknow’s favourite family restaurant for Awadhi kebabs, biryani and North Indian thalis since 2012.',
                'about' => "Spice Route started as a twelve-table eatery in Gomti Nagar and today seats 140 guests across two floors.\n\nOur kitchen is led by Ustad Rafiq Qureshi, a third-generation Awadhi chef. Galouti kebabs are made fresh every hour, the dum biryani is sealed in handi the traditional way, and every gravy is ground in-house. Private dining, birthday parties and corporate lunches are our speciality.",
                'address' => '2/45, Vibhuti Khand, Gomti Nagar, Lucknow, Uttar Pradesh 226010', 'phone' => '9415502231',
                'hours' => ['11:30', '23:00', []],
                'services' => [['Family Dine-in', 'Dining', 0, 0, 'Air-conditioned family hall with 140 covers and a kids’ menu.'], ['Party & Banquet Booking', 'Events', 650, 549, 'Per-plate packages for 25 to 120 guests, décor included.'], ['Corporate Lunch Boxes', 'Catering', 249, 219, 'Veg and non-veg lunch boxes delivered to offices across Lucknow.'], ['Home Delivery', 'Delivery', 0, 0, 'Free delivery within 5 km on orders above ₹500.']],
                'reviews' => [['Ankita Saxena', 5, 'The galouti kebab literally melts in the mouth. Our go-to place for family dinners.'], ['Rajeev Mishra', 4, 'Great biryani and quick service even on a busy Sunday.'], ['Mohd. Arif', 5, 'Booked their banquet for my son’s birthday — food and arrangements were perfect.']],
                'faqs' => [['Do you take table reservations?', 'Yes. Call or WhatsApp us; tables are held for 15 minutes past the booking time.'], ['Is there parking?', 'Free basement parking for 30 cars and valet on weekends.'], ['Do you serve Jain food?', 'Yes, a separate Jain menu is available on request.']],
            ],
            [
                'key' => 'sweets', 'month' => 0, 'city' => 'jhansi', 'partner' => 'pooja-srivastava', 'plan' => 'premium', 'renewals' => [],
                'name' => 'Shree Krishna Mishthan Bhandar', 'category' => 'Sweets & Bakery', 'sub' => 'Sweet Shop & Namkeen', 'year' => 1978,
                'owner' => ['key' => 'ramesh-agrawal', 'name' => 'Ramesh Agrawal', 'gender' => 'male', 'mobile' => '9450034412'],
                'short' => 'Pure desi-ghee sweets and fresh namkeen from Jhansi’s most trusted halwai, serving families since 1978.',
                'about' => "Three generations of the Agrawal family have run Shree Krishna Mishthan Bhandar from the same corner of Sadar Bazaar.\n\nEvery sweet is made in small batches with pure desi ghee and milk from our own dairy partners. Festive gift boxes, wedding orders and corporate Diwali hampers are packed in-house and delivered across Jhansi.",
                'address' => '58, Sadar Bazaar, near Gandhi Chowk, Jhansi, Uttar Pradesh 284001', 'phone' => '9450034412',
                'hours' => ['08:00', '22:00', []],
                'services' => [['Wedding & Bulk Orders', 'Catering', 0, 0, 'Sweets and namkeen for weddings, griha pravesh and pujas — book 3 days ahead.'], ['Corporate Diwali Hampers', 'Gifting', 1499, 1299, 'Branded gift boxes with dry fruits and assorted mithai.']],
                'own' => [['Kaju Katli (500 g)', 'Sweets', 549, 499, 120, 'gallery-0'], ['Motichoor Laddoo (1 kg)', 'Sweets', 640, 580, 80, 'gallery-1'], ['Assorted Mithai Gift Box', 'Gift Boxes', 899, 799, 60, 'about'], ['Bundelkhandi Namkeen Mix (400 g)', 'Namkeen', 220, 199, 150, 'banner']],
                'reviews' => [['Neelam Gupta', 5, 'Kaju katli is the best in Jhansi. We order every festival.'], ['Prakash Yadav', 5, 'Ordered 40 kg of sweets for my daughter’s wedding — fresh and on time.'], ['Shweta Jain', 4, 'Gift boxes are beautifully packed. Home delivery was quick.']],
                'faqs' => [['Do you deliver at home?', 'Yes, anywhere in Jhansi. Orders placed online are delivered by our own staff the same day.'], ['How long do the sweets stay fresh?', 'Milk sweets 3 days in the fridge; dry sweets and namkeen up to 3 weeks.'], ['Are your sweets made in pure ghee?', 'Yes — every sweet is made in pure desi ghee.']],
            ],
            [
                'key' => 'salon', 'month' => 1, 'city' => 'pune', 'partner' => 'nikhil-deshmukh', 'plan' => 'professional', 'renewals' => ['professional'],
                'name' => 'Glow & Grace Unisex Salon', 'category' => 'Beauty & Wellness', 'sub' => 'Unisex Salon & Bridal Studio', 'year' => 2017,
                'owner' => ['key' => 'ritika-sharma', 'name' => 'Ritika Sharma', 'gender' => 'female', 'mobile' => '9890245561'],
                'short' => 'Premium unisex salon in Kothrud — hair, skin, nails and bridal makeovers by certified stylists.',
                'about' => "Glow & Grace is a 3,000 sq. ft. studio with 12 styling stations, a private bridal suite and a skin lounge.\n\nOur stylists are trained by L’Oréal Professionnel and Schwarzkopf, and we use only sealed, branded products. Hygiene comes first: every tool is sterilised in a UV cabinet between clients.",
                'address' => 'Shop 4-6, Karve Road, Kothrud, Pune, Maharashtra 411038', 'phone' => '9890245561',
                'hours' => ['10:00', '21:00', ['tuesday']],
                'services' => [['Haircut & Styling', 'Hair', 600, 499, 'Consultation, wash, cut and blow-dry by a senior stylist.'], ['Global Hair Colour', 'Hair', 3500, 2899, 'Ammonia-free colour with a strand test and toner.'], ['Hydra Facial', 'Skin', 4500, 3499, '6-step deep cleanse, exfoliation and hydration.'], ['Bridal Makeup Package', 'Bridal', 25000, 21000, 'HD bridal makeup, hairstyling, draping and a trial session.']],
                'reviews' => [['Pallavi Gokhale', 5, 'Ritika did my bridal makeup — everyone kept asking who my artist was!'], ['Amit Joshi', 4, 'Clean place and a great haircut. Booking on WhatsApp is easy.'], ['Sayali Pawar', 5, 'The hydra facial is totally worth it. Skin glowed for days.']],
                'faqs' => [['Do I need an appointment?', 'Walk-ins are welcome, but booking on WhatsApp guarantees your slot.'], ['Do you do home bridal services?', 'Yes, within Pune for bridal packages.'], ['Which brands do you use?', 'L’Oréal Professionnel, Schwarzkopf, O3+ and Kryolan.']],
            ],
            [
                'key' => 'gym', 'month' => 1, 'city' => 'hyderabad', 'partner' => 'karthik-rao', 'plan' => 'professional', 'renewals' => ['growth-plus'],
                'name' => 'Iron Paradise Fitness Studio', 'category' => 'Fitness', 'sub' => 'Gym & Personal Training', 'year' => 2019,
                'owner' => ['key' => 'vikram-reddy', 'name' => 'Vikram Reddy', 'gender' => 'male', 'mobile' => '9908112456'],
                'short' => 'A fully equipped 8,000 sq. ft. gym in Madhapur with certified trainers, CrossFit and nutrition coaching.',
                'about' => "Iron Paradise was founded by national-level powerlifter Vikram Reddy to bring serious training to everyday people.\n\nWe have imported strength equipment, a functional training zone, steam rooms and separate changing areas. Every member gets a free body-composition analysis and a starter plan from our nutritionist.",
                'address' => '3rd Floor, Cyber Pearl Building, HITEC City Road, Madhapur, Hyderabad, Telangana 500081', 'phone' => '9908112456',
                'hours' => ['05:30', '22:30', []],
                'services' => [['Monthly Membership', 'Membership', 3000, 2499, 'Full gym access, locker and steam.'], ['Annual Membership', 'Membership', 24000, 17999, '12 months + 1 month free, includes 4 PT sessions.'], ['Personal Training (12 sessions)', 'Training', 9000, 7500, 'One-on-one coaching with a certified trainer.'], ['Diet & Nutrition Plan', 'Nutrition', 2500, 1999, 'Custom diet chart with fortnightly follow-ups.']],
                'reviews' => [['Sai Teja', 5, 'Best equipment in Madhapur and the trainers actually care about form.'], ['Divya Reddy', 5, 'Lost 11 kg in four months with their PT and diet plan.'], ['Harish Kumar', 4, 'Gets crowded at 7 pm but otherwise excellent.']],
                'faqs' => [['Is there a trial?', 'Yes, a free 3-day trial pass for first-time visitors.'], ['Are there female trainers?', 'Yes, we have two certified female trainers.'], ['Is parking available?', 'Free two-wheeler and car parking in the building.']],
            ],
            [
                'key' => 'jewellery', 'month' => 2, 'city' => 'jhansi', 'partner' => 'pooja-srivastava', 'plan' => 'growth-plus', 'renewals' => [],
                'name' => 'Swarnim Jewellers', 'category' => 'Jewellery', 'sub' => 'Gold, Diamond & Kundan Jewellery', 'year' => 1995,
                'owner' => ['key' => 'alok-soni', 'name' => 'Alok Soni', 'gender' => 'male', 'mobile' => '9415187730'],
                'short' => 'BIS-hallmarked gold, kundan and diamond jewellery with lifetime exchange, in the heart of Jhansi.',
                'about' => "Swarnim Jewellers has been crafting bridal and everyday jewellery for Bundelkhand families since 1995.\n\nEvery gold piece is BIS hallmarked with HUID, diamonds come with IGI certificates, and we offer lifetime exchange at the day’s gold rate. Custom designs are made in our own workshop.",
                'address' => '14, Manik Chowk, Jhansi, Uttar Pradesh 284002', 'phone' => '9415187730',
                'hours' => ['10:30', '20:30', ['tuesday']],
                'services' => [['Bridal Jewellery Consultation', 'Bridal', 0, 0, 'Private viewing of our bridal collection by appointment.'], ['Custom Design', 'Workshop', 0, 0, 'Bring a sketch or photo — CAD design and handcrafting in 15 days.'], ['Old Gold Exchange', 'Exchange', 0, 0, 'Transparent karat testing and exchange at the day’s rate.']],
                'reviews' => [['Kirti Soni', 5, 'Our entire wedding set was made here. Lovely craftsmanship and honest pricing.'], ['Deepak Tiwari', 5, 'Exchanged old jewellery with a proper purity test in front of us.'], ['Rashmi Agarwal', 4, 'Beautiful kundan collection. Custom earrings took 2 weeks.']],
                'faqs' => [['Is your gold hallmarked?', 'Yes, every piece is BIS hallmarked with a 6-digit HUID.'], ['Do you offer EMI?', 'Yes, on purchases above ₹50,000 with select bank cards.'], ['What are making charges?', 'From 8% on plain gold; quoted per design for kundan and polki.']],
            ],
            [
                'key' => 'pharmacy', 'month' => 2, 'city' => 'lucknow', 'partner' => 'saurabh-tiwari', 'plan' => 'premium', 'renewals' => [],
                'name' => 'MediCare Plus Pharmacy', 'category' => 'Healthcare', 'sub' => 'Pharmacy & Surgical Store', 'year' => 2010,
                'owner' => ['key' => 'abhishek-jain', 'name' => 'Abhishek Jain', 'gender' => 'male', 'mobile' => '9839087641'],
                'short' => 'Genuine medicines, surgical supplies and health devices with free home delivery across Aliganj and Kapoorthala.',
                'about' => "MediCare Plus is a licensed retail pharmacy run by B.Pharm graduates, open from 8 am to 11 pm every day.\n\nWe stock 6,000+ medicines, diabetic and cardiac care, surgical items and home-care devices. Every order is checked by a pharmacist before it leaves the store.",
                'address' => 'B-12, Sector H, Aliganj, Lucknow, Uttar Pradesh 226024', 'phone' => '9839087641',
                'hours' => ['08:00', '23:00', []],
                'services' => [['Free Home Delivery', 'Delivery', 0, 0, 'Within 4 km on orders above ₹300.'], ['BP & Sugar Check', 'Health Check', 100, 50, 'Walk-in BP and random blood sugar test at the store.']],
                'own' => [['Digital BP Monitor', 'Devices', 2499, 1849, 40, 'gallery-0'], ['Glucometer + 50 Strips', 'Diabetes Care', 1650, 1250, 60, 'about'], ['Pulse Oximeter', 'Devices', 1499, 899, 50, 'gallery-1'], ['Family First-Aid Kit', 'First Aid', 899, 699, 80, 'banner']],
                'reviews' => [['Sunil Kumar', 5, 'They deliver my father’s insulin every month without fail.'], ['Preeti Verma', 4, 'Good discounts and the pharmacist explains dosages patiently.'], ['Manoj Gupta', 5, 'Open late at night — saved us during an emergency.']],
                'faqs' => [['Do you need a prescription?', 'Yes, for scheduled medicines. Upload or WhatsApp a photo of it.'], ['Do you give discounts?', 'Flat 10–15% on most medicines and more on monthly refills.'], ['Can I return medicines?', 'Unopened strips can be returned within 7 days with the bill.']],
            ],
            [
                'key' => 'dental', 'month' => 3, 'city' => 'bengaluru', 'partner' => 'lakshmi-narayanan', 'plan' => 'professional', 'renewals' => ['professional'],
                'name' => 'SmileCare Dental Clinic', 'category' => 'Healthcare', 'sub' => 'Dental Clinic & Implant Centre', 'year' => 2015,
                'owner' => ['key' => 'divya-menon', 'name' => 'Dr. Divya Menon', 'gender' => 'female', 'mobile' => '9845521307'],
                'short' => 'Painless dentistry, implants, aligners and smile makeovers in Indiranagar by an MDS-led team.',
                'about' => "SmileCare is a four-chair clinic with digital X-ray, intra-oral scanning and a dedicated sterilisation room.\n\nDr. Divya Menon (MDS, Prosthodontics) and her team have placed 2,000+ implants. Treatments are explained with photos and a written estimate before anything begins.",
                'address' => '642, 12th Main, HAL 2nd Stage, Indiranagar, Bengaluru, Karnataka 560038', 'phone' => '9845521307',
                'hours' => ['10:00', '20:00', ['sunday']],
                'services' => [['Dental Check-up & Cleaning', 'Preventive', 1500, 999, 'Examination, scaling and polishing.'], ['Root Canal Treatment', 'Treatment', 8000, 6500, 'Single-sitting RCT with rotary instruments.'], ['Dental Implant', 'Implants', 45000, 38000, 'Swiss implant with a zirconia crown.'], ['Clear Aligners', 'Orthodontics', 150000, 120000, 'Invisible aligners with a 3D smile preview.']],
                'reviews' => [['Arjun Hegde', 5, 'Completely painless root canal. Dr. Divya explained everything.'], ['Shalini Rao', 5, 'Got aligners here — very professional and transparent pricing.'], ['Naveen Kumar', 4, 'Clean clinic, short waiting time.']],
                'faqs' => [['Is treatment painful?', 'We use computer-controlled anaesthesia, so most patients feel little or nothing.'], ['Do you accept insurance?', 'We provide all documents for reimbursement claims.'], ['Do you treat children?', 'Yes, a paediatric dentist visits every Saturday.']],
            ],
            [
                'key' => 'mobile', 'month' => 3, 'city' => 'mumbai', 'partner' => 'snehal-patil', 'plan' => 'growth', 'renewals' => ['growth', 'professional'],
                'name' => 'Galaxy Mobile World', 'category' => 'Electronics', 'sub' => 'Mobiles, Accessories & Repairs', 'year' => 2014,
                'owner' => ['key' => 'faisal-shaikh', 'name' => 'Faisal Shaikh', 'gender' => 'male', 'mobile' => '9819340028'],
                'short' => 'Latest smartphones on easy EMI, genuine accessories and same-day screen repairs in Malad.',
                'about' => "Galaxy Mobile World is an authorised multi-brand store for Samsung, Apple, OnePlus, Vivo and Oppo.\n\nWe offer zero-cost EMI, exchange offers on old phones and a repair desk with genuine parts and a 90-day warranty.",
                'address' => 'Shop 9, Natraj Market, S.V. Road, Malad West, Mumbai, Maharashtra 400064', 'phone' => '9819340028',
                'hours' => ['10:30', '21:30', []],
                'services' => [['Screen Replacement', 'Repair', 4500, 3799, 'Genuine display, fitted in 60 minutes.'], ['Phone Exchange', 'Exchange', 0, 0, 'Best value for your old phone against a new one.'], ['Data Transfer & Setup', 'Setup', 499, 299, 'Contacts, WhatsApp and photos moved to your new phone.']],
                'reviews' => [['Zoya Khan', 5, 'Got the best exchange price in Malad for my old iPhone.'], ['Ravi Nair', 4, 'Screen replaced in an hour as promised.'], ['Pooja Mehta', 5, 'Zero-cost EMI process was quick and simple.']],
                'faqs' => [['Do you offer EMI?', 'Yes, zero-cost EMI on Bajaj Finserv and major credit cards.'], ['Are your accessories original?', 'Only brand-sealed original accessories.'], ['What is the repair warranty?', '90 days on parts and labour.']],
            ],
            [
                'key' => 'coaching', 'month' => 4, 'city' => 'jhansi', 'partner' => 'pooja-srivastava', 'plan' => 'growth', 'renewals' => ['growth', 'growth'],
                'name' => 'Pinnacle Academy', 'category' => 'Education', 'sub' => 'Coaching Institute (Class 6–12, JEE & NEET)', 'year' => 2011,
                'owner' => ['key' => 'manoj-tripathi', 'name' => 'Prof. Manoj Tripathi', 'gender' => 'male', 'mobile' => '9935067712'],
                'short' => 'Small-batch coaching for Classes 6–12, JEE and NEET with weekly tests and parent reports.',
                'about' => "Pinnacle Academy limits every batch to 30 students so each child gets attention.\n\nOur faculty of 14 includes IIT and NIT alumni. Weekly tests, doubt sessions every evening and a monthly parent-teacher meeting keep progress visible. 63 of our students cleared JEE Main last year.",
                'address' => '2nd Floor, Elite Complex, Elite Chauraha, Jhansi, Uttar Pradesh 284001', 'phone' => '9935067712',
                'hours' => ['07:00', '20:00', ['sunday']],
                'services' => [['Foundation (Class 6–10)', 'School', 30000, 26000, 'Maths and Science, CBSE and UP Board.'], ['JEE Main + Advanced (2 years)', 'Engineering', 145000, 125000, 'Physics, Chemistry and Maths with test series.'], ['NEET (2 years)', 'Medical', 140000, 120000, 'Biology, Physics and Chemistry with NCERT focus.']],
                'reviews' => [['Saurabh Dwivedi', 5, 'My son improved from 62% to 91% in maths in one year.'], ['Anjali Rai', 5, 'Teachers are approachable and the weekly tests really help.'], ['Vivek Kushwaha', 4, 'Good faculty, the batch timings are convenient.']],
                'faqs' => [['Is there a scholarship test?', 'Yes, the Pinnacle Scholarship Test every April gives up to 90% fee waiver.'], ['What is the batch size?', 'A maximum of 30 students.'], ['Are classes available online?', 'Recorded lectures are available to enrolled students.']],
            ],
            [
                'key' => 'realestate', 'month' => 4, 'city' => 'pune', 'partner' => 'nikhil-deshmukh', 'plan' => 'growth-plus', 'renewals' => [],
                'name' => 'Dream Nest Realty', 'category' => 'Real Estate', 'sub' => 'Property Consultants', 'year' => 2008,
                'owner' => ['key' => 'sunita-deshpande', 'name' => 'Sunita Deshpande', 'gender' => 'female', 'mobile' => '9822413395'],
                'short' => 'RERA-registered property consultants for flats, villas and commercial spaces across West Pune.',
                'about' => "Dream Nest Realty has helped more than 1,800 families buy, sell and rent homes in Pune.\n\nWe work only with RERA-registered projects, handle home-loan paperwork end to end, and never charge buyers a brokerage on new projects.",
                'address' => 'Office 11, Sai Capital, Baner Road, Pune, Maharashtra 411045', 'phone' => '9822413395',
                'hours' => ['10:00', '19:30', ['tuesday']],
                'services' => [['New Home Buying', 'Buy', 0, 0, 'Shortlisting, site visits and price negotiation — free for buyers.'], ['Resale & Rentals', 'Sell / Rent', 0, 0, 'Verified tenants and buyers with legal paperwork.'], ['Home Loan Assistance', 'Finance', 0, 0, 'Tie-ups with SBI, HDFC and ICICI for fast approvals.']],
                'reviews' => [['Kunal Bhide', 5, 'Found our 2BHK in Baner within budget. Very honest team.'], ['Aarti Kale', 5, 'Handled the whole home-loan process for us.'], ['Sameer Kulkarni', 4, 'Good options, though weekend site visits were packed.']],
                'faqs' => [['Do buyers pay brokerage?', 'No brokerage on new projects; 1% on resale.'], ['Are projects RERA registered?', 'We only list RERA-registered projects.'], ['Can NRIs buy through you?', 'Yes, with power-of-attorney and video site visits.']],
            ],
            [
                'key' => 'cafe', 'month' => 5, 'city' => 'bengaluru', 'partner' => 'lakshmi-narayanan', 'plan' => 'premium', 'renewals' => [],
                'name' => 'Brew Bay Café', 'category' => 'Restaurants & Food', 'sub' => 'Specialty Coffee & Bakery', 'year' => 2020,
                'owner' => ['key' => 'ananya-rao', 'name' => 'Ananya Rao', 'gender' => 'female', 'mobile' => '9880365142'],
                'short' => 'Single-origin Karnataka coffee, fresh bakes and a quiet place to work in Koramangala.',
                'about' => "Brew Bay roasts beans from estates in Chikmagalur and Coorg every week in small batches.\n\nOur bakery turns out sourdough, croissants and cakes daily. Fast Wi-Fi, plug points at every table and a quiet upstairs room make us a favourite with Bengaluru’s freelancers.",
                'address' => '80 Feet Road, 4th Block, Koramangala, Bengaluru, Karnataka 560034', 'phone' => '9880365142',
                'hours' => ['08:00', '23:00', []],
                'services' => [['Coffee Brewing Workshop', 'Workshop', 1800, 1500, 'Two-hour hands-on class on pour-over and French press.'], ['Custom Celebration Cakes', 'Bakery', 1200, 999, 'Eggless options; order 24 hours ahead.']],
                'own' => [['Chikmagalur Estate Coffee Beans (250 g)', 'Coffee', 650, 549, 90, 'gallery-0'], ['Filter Coffee Powder (500 g)', 'Coffee', 480, 420, 120, 'gallery-1'], ['Brew Bay Ceramic Mug', 'Merchandise', 499, 399, 40, 'about']],
                'reviews' => [['Rohan Iyer', 5, 'Best pour-over in Koramangala and the sourdough is great.'], ['Meghna Shetty', 4, 'Lovely place to work from. Gets busy on weekends.'], ['Karan Malhotra', 5, 'Their estate beans made me give up instant coffee for good.']],
                'faqs' => [['Is there Wi-Fi?', 'Yes, free 100 Mbps Wi-Fi for customers.'], ['Do you have vegan options?', 'Oat and almond milk, plus vegan cakes on weekends.'], ['Do you ship beans?', 'We deliver across Bengaluru through our online store.']],
            ],
            [
                'key' => 'yoga', 'month' => 6, 'city' => 'pune', 'partner' => 'nikhil-deshmukh', 'plan' => 'growth', 'renewals' => ['growth'],
                'name' => 'Prana Yoga Studio', 'category' => 'Fitness', 'sub' => 'Yoga & Meditation Studio', 'year' => 2016,
                'owner' => ['key' => 'aditi-kulkarni', 'name' => 'Aditi Kulkarni', 'gender' => 'female', 'mobile' => '9763140285'],
                'short' => 'Hatha, Ashtanga and prenatal yoga with certified teachers in a calm Aundh studio.',
                'about' => "Prana Yoga Studio was started by Aditi Kulkarni after ten years of study in Rishikesh and Mysuru.\n\nClasses are small, props are provided, and beginners are always welcome. We also run therapy batches for back pain, PCOD and diabetes.",
                'address' => 'Plot 22, ITI Road, Aundh, Pune, Maharashtra 411007', 'phone' => '9763140285',
                'hours' => ['06:00', '20:30', ['sunday']],
                'services' => [['Monthly Group Classes', 'Classes', 2500, 2200, '12 sessions a month, morning or evening.'], ['Prenatal Yoga', 'Specialised', 3500, 3000, 'Safe practice for every trimester.'], ['Therapy Yoga (1:1)', 'Therapy', 6000, 5000, '8 private sessions for back pain, PCOD or diabetes.']],
                'reviews' => [['Neha Bapat', 5, 'Prenatal classes made my pregnancy so much easier.'], ['Rajesh Pillai', 5, 'Back pain gone after two months of therapy yoga.'], ['Shruti Apte', 4, 'Peaceful studio and patient teachers.']],
                'faqs' => [['I am a complete beginner — can I join?', 'Yes, every batch has beginner variations.'], ['Do I need my own mat?', 'Mats, blocks and straps are provided.'], ['Is there a trial class?', 'Your first class is free.']],
            ],
            [
                'key' => 'grocery', 'month' => 6, 'city' => 'lucknow', 'partner' => 'imran-siddiqui', 'plan' => 'growth', 'renewals' => ['reject'],
                'name' => 'Annapurna Kirana Store', 'category' => 'Grocery', 'sub' => 'Kirana & Daily Needs', 'year' => 2002,
                'owner' => ['key' => 'sudha-jaiswal', 'name' => 'Sudha Jaiswal', 'gender' => 'female', 'mobile' => '9918247703'],
                'short' => 'Groceries, staples and daily needs with free home delivery in Indira Nagar.',
                'about' => "Annapurna has been the neighbourhood kirana of Indira Nagar for over twenty years.\n\nWe stock fresh atta, pulses, spices, dairy and household items, and deliver monthly ration lists to your door. Udhaar khata for regular families.",
                'address' => 'C-18, Sector 11, Indira Nagar, Lucknow, Uttar Pradesh 226016', 'phone' => '9918247703',
                'hours' => ['07:00', '22:00', []],
                'services' => [['Monthly Ration Delivery', 'Delivery', 0, 0, 'Send your list on WhatsApp; delivered the same evening.'], ['Fresh Chakki Atta', 'Staples', 0, 0, 'Wheat ground fresh in-store twice a week.']],
                'reviews' => [['Rekha Pandey', 5, 'Fresh atta and honest weights, every time.'], ['Ajay Singh', 4, 'Quick home delivery and polite staff.'], ['Farida Begum', 5, 'They remember my monthly list — very convenient.']],
                'faqs' => [['Is home delivery free?', 'Free within 3 km on orders above ₹500.'], ['Do you accept UPI?', 'Yes, all UPI apps and cards.'], ['Can I order on WhatsApp?', 'Yes, send your list to our number.']],
            ],
            [
                'key' => 'optical', 'month' => 7, 'city' => 'hyderabad', 'partner' => 'karthik-rao', 'plan' => 'professional', 'renewals' => [],
                'name' => 'Clear Vision Opticals', 'category' => 'Healthcare', 'sub' => 'Optical Store & Eye Testing', 'year' => 2009,
                'owner' => ['key' => 'sameer-ali', 'name' => 'Sameer Ali', 'gender' => 'male', 'mobile' => '9989027713'],
                'short' => 'Computerised eye testing, branded frames and contact lenses at fair prices in Ameerpet.',
                'about' => "Clear Vision is run by qualified optometrist Sameer Ali with 15 years of experience.\n\nFree computerised eye tests, 1,500+ frames from Ray-Ban, Titan and Vincent Chase, and lenses from Essilor and Zeiss — usually ready in 24 hours.",
                'address' => '7-1-28, Ameerpet Main Road, Hyderabad, Telangana 500016', 'phone' => '9989027713',
                'hours' => ['10:00', '21:00', []],
                'services' => [['Free Eye Test', 'Eye Test', 300, 0, 'Computerised refraction by a qualified optometrist.'], ['Blue-Cut Lenses', 'Lenses', 3500, 2499, 'Anti-glare blue-light lenses for screen users.'], ['Contact Lens Fitting', 'Contacts', 800, 500, 'Trial lenses and fitting consultation.']],
                'reviews' => [['Nikhila Reddy', 5, 'Thorough eye test and great frame collection.'], ['Abdul Rahman', 4, 'Glasses ready in a day as promised.'], ['Sravani Goud', 5, 'Very patient with my kid’s first glasses.']],
                'faqs' => [['Is the eye test really free?', 'Yes, always.'], ['How long do glasses take?', 'Single-vision in 24 hours; progressives in 3–4 days.'], ['Do you repair frames?', 'Minor repairs are free for our customers.']],
            ],
            [
                'key' => 'furniture', 'month' => 8, 'city' => 'lucknow', 'partner' => 'imran-siddiqui', 'plan' => 'growth-plus', 'renewals' => [],
                'name' => 'Royal Wood Furniture', 'category' => 'Home & Living', 'sub' => 'Furniture Showroom', 'year' => 2005,
                'owner' => ['key' => 'rekha-gupta', 'name' => 'Rekha Gupta', 'gender' => 'female', 'mobile' => '9839570024'],
                'short' => 'Solid sheesham and teak furniture, sofas and modular kitchens made in our own Lucknow workshop.',
                'about' => "Royal Wood designs and builds furniture in its 20,000 sq. ft. workshop in Kursi Road.\n\nFrom sofas and beds to dining sets and modular kitchens, every piece is made from seasoned wood with a 5-year warranty and free installation within Lucknow.",
                'address' => 'Kursi Road, near Integral University, Lucknow, Uttar Pradesh 226026', 'phone' => '9839570024',
                'hours' => ['10:00', '20:30', ['tuesday']],
                'services' => [['Custom Furniture', 'Custom', 0, 0, 'Made-to-measure furniture from your design.'], ['Modular Kitchen', 'Kitchens', 185000, 159000, 'L-shaped kitchen with soft-close fittings, 10-year warranty.'], ['Sofa Re-upholstery', 'Repair', 9000, 7500, 'New foam and fabric for a 3+2 sofa set.']],
                'reviews' => [['Vinod Srivastava', 5, 'Our sheesham dining set is solid and beautifully finished.'], ['Shalini Kapoor', 4, 'Modular kitchen came out great; took a week longer than promised.'], ['Arun Mishra', 5, 'Free installation and very polite team.']],
                'faqs' => [['Is delivery free?', 'Free delivery and installation within Lucknow.'], ['What is the warranty?', '5 years on solid-wood furniture.'], ['Can I customise sizes?', 'Yes, every design can be built to your size.']],
            ],
            [
                'key' => 'travel', 'month' => 8, 'city' => 'lucknow', 'partner' => 'saurabh-tiwari', 'plan' => 'growth', 'renewals' => ['growth'],
                'name' => 'Yatra Tours & Travels', 'category' => 'Travel', 'sub' => 'Tour Operator & Ticketing', 'year' => 2013,
                'owner' => ['key' => 'harpreet-singh', 'name' => 'Harpreet Singh', 'gender' => 'male', 'mobile' => '9792034418'],
                'short' => 'Holiday packages, pilgrimages, flight and train tickets, and visa help from Lucknow.',
                'about' => "Yatra Tours & Travels plans 1,200+ trips a year — from Char Dham yatras and Golden Triangle tours to Dubai and Thailand holidays.\n\nWe handle tickets, hotels, cabs and visas, with a 24×7 helpline while you travel.",
                'address' => '15, Kapoorthala Complex, Aliganj, Lucknow, Uttar Pradesh 226024', 'phone' => '9792034418',
                'hours' => ['10:00', '20:00', []],
                'services' => [['Golden Triangle Tour (5N/6D)', 'Holiday', 32000, 27999, 'Delhi–Agra–Jaipur with hotels, cab and guide.'], ['Char Dham Yatra', 'Pilgrimage', 45000, 39999, '10-day package with helicopter option.'], ['Dubai Holiday (4N/5D)', 'International', 65000, 57999, 'Flights, visa, hotel and desert safari.']],
                'reviews' => [['Neha Tandon', 5, 'Golden Triangle trip was perfectly organised.'], ['Rakesh Bajpai', 5, 'Took my parents on Char Dham — the team handled everything.'], ['Sana Rizvi', 4, 'Dubai visa came through in 3 days.']],
                'faqs' => [['Can packages be customised?', 'Yes, every itinerary can be changed.'], ['Do you book trains and flights?', 'Yes, at standard fares plus a small service fee.'], ['What is the cancellation policy?', 'Shared in writing with every booking.']],
            ],
            [
                'key' => 'florist', 'month' => 9, 'city' => 'mumbai', 'partner' => 'rahul-shinde', 'plan' => 'premium', 'renewals' => [],
                'name' => 'Petals & Blooms', 'category' => 'Gifts & Flowers', 'sub' => 'Florist & Event Décor', 'year' => 2016,
                'owner' => ['key' => 'kiran-bhosale', 'name' => 'Kiran Bhosale', 'gender' => 'female', 'mobile' => '9867240193'],
                'short' => 'Fresh flower bouquets, gift hampers and wedding décor with same-day delivery across Mumbai.',
                'about' => "Petals & Blooms sources fresh flowers from Dadar flower market every morning.\n\nWe make hand-tied bouquets, flower boxes and gift hampers, and decorate weddings, haldi and mehendi functions across Mumbai.",
                'address' => 'Shop 3, Hill Road, Bandra West, Mumbai, Maharashtra 400050', 'phone' => '9867240193',
                'hours' => ['08:00', '22:00', []],
                'services' => [['Wedding & Haldi Décor', 'Décor', 75000, 65000, 'Mandap, entrance and stage décor with fresh flowers.'], ['Midnight Delivery', 'Delivery', 499, 399, 'Bouquets delivered between 11 pm and 1 am.']],
                'own' => [['Red Roses Bouquet (20 stems)', 'Bouquets', 1299, 999, 50, 'gallery-0'], ['Mixed Flowers Basket', 'Baskets', 1799, 1499, 30, 'about'], ['Orchid Flower Box', 'Flower Boxes', 2499, 2199, 20, 'gallery-1'], ['Seasonal Blooms Bouquet', 'Bouquets', 999, 849, 60, 'banner']],
                'reviews' => [['Tanvi Desai', 5, 'Surprise bouquet reached exactly at midnight. Fresh and gorgeous.'], ['Aditya Kamat', 5, 'Haldi décor was stunning and on budget.'], ['Fatima Sayed', 4, 'Lovely orchid box, delivery was a little late.']],
                'faqs' => [['Do you deliver the same day?', 'Yes, for orders before 6 pm anywhere in Mumbai.'], ['Can I add a message card?', 'Yes, free with every order.'], ['Do you do corporate orders?', 'Yes, weekly office flowers and event décor.']],
            ],
            [
                'key' => 'garage', 'month' => 10, 'city' => 'hyderabad', 'partner' => 'karthik-rao', 'plan' => 'growth', 'renewals' => [],
                'name' => 'AutoCare Car Service', 'category' => 'Automobile', 'sub' => 'Multi-brand Car Service', 'year' => 2011,
                'owner' => ['key' => 'mohan-lal', 'name' => 'Mohan Lal', 'gender' => 'male', 'mobile' => '9848061127'],
                'short' => 'Multi-brand car servicing, denting-painting and wheel alignment with free pick-up in Kukatpally.',
                'about' => "AutoCare is a 12-bay workshop with computerised diagnostics and a dust-free paint booth.\n\nWe service every make from Maruti to Mercedes using genuine or OEM parts, with free pick-up and drop and a 1,000 km service warranty.",
                'address' => 'Plot 9, IDA Kukatpally, Hyderabad, Telangana 500072', 'phone' => '9848061127',
                'hours' => ['09:00', '19:30', ['sunday']],
                'services' => [['Periodic Car Service', 'Service', 4999, 3999, 'Oil, filters and a 40-point inspection.'], ['Wheel Alignment & Balancing', 'Tyres', 1200, 899, 'Computerised 3D alignment.'], ['Denting & Painting (per panel)', 'Body Shop', 3500, 2800, 'Dust-free booth with factory colour match.']],
                'reviews' => [['Srikanth Rao', 5, 'Free pick-up and the car came back spotless.'], ['Lavanya Prasad', 4, 'Transparent bill with photos of every part replaced.'], ['Imtiaz Ahmed', 5, 'Fixed an AC problem two dealers couldn’t.']],
                'faqs' => [['Do you pick up the car?', 'Free pick-up and drop within 10 km.'], ['Are parts genuine?', 'Genuine or OEM parts only, with bills.'], ['Is there a warranty?', '1,000 km or 30 days on every service.']],
            ],
            [
                'key' => 'photo', 'month' => 10, 'city' => 'mumbai', 'partner' => 'rahul-shinde', 'plan' => 'professional', 'renewals' => [],
                'name' => 'Frame Studio Photography', 'category' => 'Photography', 'sub' => 'Wedding & Portrait Studio', 'year' => 2015,
                'owner' => ['key' => 'debashish-das', 'name' => 'Debashish Das', 'gender' => 'male', 'mobile' => '9820518846'],
                'short' => 'Candid wedding photography, pre-wedding shoots, portraits and product photography in Andheri.',
                'about' => "Frame Studio is a team of eight photographers and cinematographers who have covered 600+ weddings.\n\nOur Andheri studio has a cyclorama wall, professional lighting and a makeup room for portrait, maternity and product shoots.",
                'address' => 'Unit 14, Laxmi Industrial Estate, New Link Road, Andheri West, Mumbai, Maharashtra 400053', 'phone' => '9820518846',
                'hours' => ['10:00', '20:00', []],
                'services' => [['Wedding Photography (2 days)', 'Wedding', 150000, 125000, 'Candid + traditional photos, cinematic film and album.'], ['Pre-wedding Shoot', 'Pre-wedding', 45000, 38000, 'One location, two outfits, 40 edited photos.'], ['Product Photography (per 10 products)', 'Commercial', 8000, 6500, 'White-background e-commerce photos.']],
                'reviews' => [['Riya & Kunal', 5, 'Our wedding film made everyone cry. Worth every rupee.'], ['Hetal Shah', 5, 'Product photos for my store doubled our online sales.'], ['Vishal Patil', 4, 'Great pre-wedding shoot; album took 6 weeks.']],
                'faqs' => [['How soon do we get photos?', 'Previews in 48 hours; full gallery in 3 weeks.'], ['Do you travel for weddings?', 'Yes, across India and abroad.'], ['Can we rent the studio?', 'Yes, by the hour with lights included.']],
            ],
        ];
    }

    /** Employees: department, designation, salary (monthly gross), reporting manager key. */
    public static function employees(): array
    {
        return [
            ['key' => 'deepak-sharma', 'name' => 'Deepak Sharma', 'mobile' => '9810233419', 'gender' => 'male', 'dob' => '1983-07-19', 'blood' => 'B+', 'marital' => 'married', 'dept' => 'Operations', 'designation' => 'Operations Manager', 'joined' => '2021-04-05', 'gross' => 72000, 'manager' => null, 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'ritu-agarwal', 'name' => 'Ritu Agarwal', 'mobile' => '9839442210', 'gender' => 'female', 'dob' => '1987-03-08', 'blood' => 'O+', 'marital' => 'married', 'dept' => 'Human Resources', 'designation' => 'HR Manager', 'joined' => '2022-01-10', 'gross' => 58000, 'manager' => null, 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'manish-gupta', 'name' => 'Manish Gupta', 'mobile' => '9450319922', 'gender' => 'male', 'dob' => '1985-12-02', 'blood' => 'A+', 'marital' => 'married', 'dept' => 'Accounts & Finance', 'designation' => 'Senior Accountant', 'joined' => '2021-08-16', 'gross' => 42000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'vivek-pathak', 'name' => 'Vivek Pathak', 'mobile' => '9918302215', 'gender' => 'male', 'dob' => '1994-06-28', 'blood' => 'B+', 'marital' => 'single', 'dept' => 'Accounts & Finance', 'designation' => 'Accounts Executive', 'joined' => '2023-06-01', 'gross' => 26000, 'manager' => 'manish-gupta', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'aman-khan', 'name' => 'Aman Khan', 'mobile' => '9839771045', 'gender' => 'male', 'dob' => '1996-10-14', 'blood' => 'AB+', 'marital' => 'single', 'dept' => 'Technology', 'designation' => 'Full Stack Developer', 'joined' => '2022-09-12', 'gross' => 60000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'shruti-pandey', 'name' => 'Shruti Pandey', 'mobile' => '9792218834', 'gender' => 'female', 'dob' => '1995-02-09', 'blood' => 'O-', 'marital' => 'single', 'dept' => 'Marketing', 'designation' => 'Marketing Executive', 'joined' => '2023-02-20', 'gross' => 32000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'anjali-saxena', 'name' => 'Anjali Saxena', 'mobile' => '9450887126', 'gender' => 'female', 'dob' => '1997-05-03', 'blood' => 'A+', 'marital' => 'single', 'dept' => 'Marketing', 'designation' => 'Graphic Designer', 'joined' => '2024-01-08', 'gross' => 28000, 'manager' => 'shruti-pandey', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'tanya-kapoor', 'name' => 'Tanya Kapoor', 'mobile' => '9918460372', 'gender' => 'female', 'dob' => '1999-08-17', 'blood' => 'B-', 'marital' => 'single', 'dept' => 'Marketing', 'designation' => 'Social Media Executive', 'joined' => '2026-03-02', 'gross' => 22000, 'manager' => 'shruti-pandey', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'rohan-mehta', 'name' => 'Rohan Mehta', 'mobile' => '9935124478', 'gender' => 'male', 'dob' => '1993-11-21', 'blood' => 'O+', 'marital' => 'married', 'dept' => 'Sales & Partnerships', 'designation' => 'Partner Development Executive', 'joined' => '2023-09-04', 'gross' => 30000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'full_time', 'field' => true],
            ['key' => 'gurpreet-singh', 'name' => 'Gurpreet Singh', 'mobile' => '9839615503', 'gender' => 'male', 'dob' => '1990-01-15', 'blood' => 'B+', 'marital' => 'married', 'dept' => 'Sales & Partnerships', 'designation' => 'Field Sales Officer', 'joined' => '2024-05-13', 'gross' => 27000, 'manager' => 'rohan-mehta', 'city' => 'jhansi', 'type' => 'full_time', 'field' => true],
            ['key' => 'priyanka-mishra', 'name' => 'Priyanka Mishra', 'mobile' => '9792503361', 'gender' => 'female', 'dob' => '1998-04-26', 'blood' => 'A-', 'marital' => 'single', 'dept' => 'Customer Support', 'designation' => 'Customer Support Executive', 'joined' => '2024-07-01', 'gross' => 20000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'neha-yadav', 'name' => 'Neha Yadav', 'mobile' => '9450276690', 'gender' => 'female', 'dob' => '1999-12-30', 'blood' => 'O+', 'marital' => 'single', 'dept' => 'Customer Support', 'designation' => 'Customer Support Executive', 'joined' => '2025-02-03', 'gross' => 19000, 'manager' => 'priyanka-mishra', 'city' => 'lucknow', 'type' => 'full_time'],
            ['key' => 'pallavi-dubey', 'name' => 'Pallavi Dubey', 'mobile' => '9918035547', 'gender' => 'female', 'dob' => '2000-09-11', 'blood' => 'AB-', 'marital' => 'single', 'dept' => 'Operations', 'designation' => 'Data Entry Operator', 'joined' => '2026-06-15', 'gross' => 16000, 'manager' => 'deepak-sharma', 'city' => 'lucknow', 'type' => 'contract'],
            ['key' => 'harsh-vardhan', 'name' => 'Harsh Vardhan', 'mobile' => '9839208814', 'gender' => 'male', 'dob' => '2001-03-19', 'blood' => 'B+', 'marital' => 'single', 'dept' => 'Operations', 'designation' => 'Office Assistant', 'joined' => '2024-11-18', 'gross' => 14500, 'manager' => 'ritu-agarwal', 'city' => 'lucknow', 'type' => 'full_time', 'exit' => ['2026-07-31', 'Resigned — moving to Delhi for higher studies']],
        ];
    }

    /** Department => designations. */
    public static function departments(): array
    {
        return [
            'Operations' => ['Operations Manager', 'Data Entry Operator', 'Office Assistant'],
            'Human Resources' => ['HR Manager', 'HR Executive'],
            'Accounts & Finance' => ['Senior Accountant', 'Accounts Executive'],
            'Technology' => ['Full Stack Developer'],
            'Marketing' => ['Marketing Executive', 'Graphic Designer', 'Social Media Executive'],
            'Sales & Partnerships' => ['Partner Development Executive', 'Field Sales Officer'],
            'Customer Support' => ['Customer Support Executive'],
        ];
    }

    /** National and festival holidays across the demo year (dates as observed in 2025–26). */
    public static function holidays(): array
    {
        return [
            '2025-12-25' => 'Christmas',
            '2026-01-26' => 'Republic Day',
            '2026-03-04' => 'Holi',
            '2026-03-21' => 'Eid-ul-Fitr',
            '2026-04-03' => 'Good Friday',
            '2026-04-14' => 'Dr. Ambedkar Jayanti',
            '2026-08-15' => 'Independence Day',
            '2026-08-28' => 'Raksha Bandhan',
            '2026-09-04' => 'Janmashtami',
            '2026-10-02' => 'Gandhi Jayanti',
            '2026-10-20' => 'Dussehra',
            '2026-11-09' => 'Diwali (Govardhan Puja)',
        ];
    }

    /** Company catalogue products sold through VIP pages (earn upline commission). */
    public static function catalogueProducts(): array
    {
        return [
            ['slug' => 'rose-oud-edp', 'name' => 'Rose Oud Eau de Parfum 100 ml', 'category' => 'Fragrance', 'price' => 2499, 'offer' => 1899, 'sku' => 'GLF-ROUD-100', 'badge' => 'Bestseller', 'short' => 'A rich Turkish rose wrapped in smoky oud and amber — long-lasting evening fragrance.'],
            ['slug' => 'citrus-amber-edp', 'name' => 'Citrus Amber Eau de Parfum 50 ml', 'category' => 'Fragrance', 'price' => 1599, 'offer' => 1199, 'sku' => 'GLF-CAMB-050', 'badge' => null, 'short' => 'Fresh bergamot and orange over a warm amber base. Office-friendly, all-day wear.'],
            ['slug' => 'velvet-musk-edp', 'name' => 'Velvet Musk Eau de Parfum 100 ml', 'category' => 'Fragrance', 'price' => 1999, 'offer' => 1499, 'sku' => 'GLF-VMSK-100', 'badge' => 'New', 'short' => 'Soft white musk with sandalwood and vanilla — a clean signature scent.'],
            ['slug' => 'blush-petal-edp', 'name' => 'Blush Petal Eau de Parfum for Her 50 ml', 'category' => 'Fragrance', 'price' => 1799, 'offer' => 1299, 'sku' => 'GLF-BPTL-050', 'badge' => null, 'short' => 'Peony, lychee and pink pepper — a bright floral for everyday wear.'],
            ['slug' => 'ashwagandha-capsules', 'name' => 'Ashwagandha Vitality Capsules (60)', 'category' => 'Herbal Wellness', 'price' => 699, 'offer' => 549, 'sku' => 'GLW-ASHW-060', 'badge' => 'Bestseller', 'short' => 'KSM-66 ashwagandha root extract for stress, stamina and better sleep.'],
            ['slug' => 'ayur-immunity-kit', 'name' => 'Ayur Immunity Kit', 'category' => 'Wellness', 'price' => 1299, 'offer' => 999, 'sku' => 'GLW-IMMK-001', 'badge' => null, 'short' => 'Giloy, tulsi and amla capsules with chyawanprash — a 30-day immunity routine.'],
            ['slug' => 'ginkgo-memory-capsules', 'name' => 'Ginkgo Memory Capsules (60)', 'category' => 'Herbal Wellness', 'price' => 849, 'offer' => 649, 'sku' => 'GLW-GINK-060', 'badge' => null, 'short' => 'Ginkgo biloba with brahmi for focus and memory support.'],
        ];
    }

    /** Shopper first names / surnames — combined into ~70 customers. */
    public static function shopperNames(): array
    {
        return [
            'first' => ['Aarav', 'Vihaan', 'Aditya', 'Arnav', 'Kabir', 'Reyansh', 'Ishaan', 'Rudra', 'Yash', 'Siddharth', 'Kunal', 'Varun', 'Nitin', 'Gaurav', 'Akash', 'Abhinav', 'Ayaan', 'Harsh', 'Mayank', 'Tarun',
                'Aanya', 'Diya', 'Ananya', 'Saanvi', 'Myra', 'Ira', 'Kavya', 'Riya', 'Nisha', 'Swati', 'Megha', 'Pooja', 'Shreya', 'Tanvi', 'Isha', 'Aditi', 'Kriti', 'Neha', 'Bhavna', 'Komal'],
            'last' => ['Sharma', 'Verma', 'Gupta', 'Singh', 'Mishra', 'Tiwari', 'Yadav', 'Patel', 'Joshi', 'Kulkarni', 'Deshpande', 'Reddy', 'Rao', 'Nair', 'Iyer', 'Menon', 'Shetty', 'Agarwal', 'Saxena', 'Srivastava', 'Chauhan', 'Khan', 'Siddiqui', 'Pawar', 'Shinde'],
        ];
    }

    /** Pincode + locality per city, for addresses. */
    public static function localities(): array
    {
        return [
            'lucknow' => ['Uttar Pradesh', [['226010', 'Gomti Nagar'], ['226016', 'Indira Nagar'], ['226024', 'Aliganj'], ['226012', 'Ashiyana'], ['226001', 'Hazratganj']]],
            'jhansi' => ['Uttar Pradesh', [['284001', 'Civil Lines'], ['284002', 'Sipri Bazar'], ['284003', 'Nandanpura'], ['284128', 'Medical College Road']]],
            'pune' => ['Maharashtra', [['411038', 'Kothrud'], ['411045', 'Baner'], ['411007', 'Aundh'], ['411014', 'Viman Nagar'], ['411057', 'Hinjewadi']]],
            'mumbai' => ['Maharashtra', [['400064', 'Malad West'], ['400053', 'Andheri West'], ['400050', 'Bandra West'], ['400076', 'Powai'], ['400092', 'Borivali West']]],
            'hyderabad' => ['Telangana', [['500081', 'Madhapur'], ['500032', 'Gachibowli'], ['500072', 'Kukatpally'], ['500016', 'Ameerpet'], ['500034', 'Banjara Hills']]],
            'bengaluru' => ['Karnataka', [['560038', 'Indiranagar'], ['560034', 'Koramangala'], ['560066', 'Whitefield'], ['560041', 'Jayanagar'], ['560102', 'HSR Layout']]],
        ];
    }
}
