-- Homepage: replace the "The People Behind Global Life" team section with the "Who We Are" section
-- (owner photo + quote card, text, stats, buttons) and set the FSSAI license number.
-- Run in phpMyAdmin AFTER deploying the code. Safe to run more than once. (MariaDB)
-- The photo ships with the code at storage/app/public/uploads/home/owner-photo.webp.
-- Everything is editable later in Admin -> Homepage Builder and Admin -> Site Settings -> Contact.
SET NAMES utf8mb4;

-- 1. Schema (the same change as migration 2026_11_13_000001_add_who_we_are_to_home_sections).
ALTER TABLE `home_sections` MODIFY `type` ENUM('hero','about','features','stats','cta','founder_quote','products_showcase',
    'testimonials_showcase','presence_map','business_opportunity','process_steps','vip_plans',
    'blog_showcase','upcoming_events','gallery','enquiry_form','team','certifications','quality',
    'category_products','products_by_category','who_we_are') NOT NULL;
ALTER TABLE `home_sections` ADD COLUMN IF NOT EXISTS `quote` TEXT NULL AFTER `content`;
ALTER TABLE `home_sections` ADD COLUMN IF NOT EXISTS `quote_author` VARCHAR(150) NULL AFTER `quote`;

-- Record the migration so a later `php artisan migrate` does not try to run it again.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_13_000001_add_who_we_are_to_home_sections', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_13_000001_add_who_we_are_to_home_sections');

-- 2. Add the Who We Are section in the team section's place (or at the end if there is no team section).
SET @order := COALESCE(
    (SELECT MIN(`display_order`) FROM `home_sections` WHERE `type` = 'team'),
    (SELECT MAX(`display_order`) + 1 FROM `home_sections`),
    1);

INSERT INTO `home_sections`
    (`type`, `title`, `subtitle`, `content`, `quote`, `quote_author`, `image_path`, `cta_label`, `cta_url`, `items`, `status`, `display_order`, `created_at`, `updated_at`)
SELECT 'who_we_are',
    'A homegrown brand built on transparency, quality, and people',
    'Who We Are',
    '<p>Global Life was founded with one clear belief: premium wellness and lifestyle products should be accessible to every Indian family — not just those in metros. We design, test, and distribute products that genuinely work, offered through an ethical, transparent business model.</p><p>Today, our range spans herbal vitality supplements, high-quality protein nutrition, and luxury organic fragrances — all manufactured under strict quality protocols and shared by thousands of trusted distributors across India.</p>',
    'We built Global Life on one principle — if the product doesn''t genuinely help people, we won''t sell it.',
    'Founder, Global Life',
    'uploads/home/owner-photo.webp',
    'View Our Products',
    '/products',
    '[{"label":"Years of Trust","value":"10+"},{"label":"Happy Customers","value":"2.5L+"},{"label":"Cities Served","value":"500+"}]',
    'active', @order, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `home_sections` WHERE `type` = 'who_we_are');

-- 3. Remove the team section.
DELETE FROM `home_sections` WHERE `type` = 'team';

-- 4. The old plain "About Us" block has the same heading; hide it (turn it back on from the Homepage Builder if wanted).
UPDATE `home_sections` SET `status` = 'inactive', `updated_at` = NOW()
WHERE `type` = 'about' AND `title` = 'A homegrown brand built on transparency, quality, and people';

-- 5. FSSAI license number: the "Trusted & Certified" badge on the homepage ...
UPDATE `home_sections`
SET `items` = REPLACE(`items`, 'Lic. 10012345678', 'Lic. No. 22726619000081'), `updated_at` = NOW()
WHERE `type` = 'certifications' AND `items` LIKE '%Lic. 10012345678%';

-- ... and the footer badge (Admin -> Site Settings -> Contact -> FSSAI License No.).
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES ('fssai_license', '22726619000081', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();

-- Settings are cached forever; drop the cached copy so the footer shows the number.
-- (Only works with CACHE_STORE=database. Otherwise run: php artisan cache:clear.)
DELETE FROM `cache` WHERE `key` LIKE '%site-settings';

-- Check
SELECT `id`, `type`, `title`, `status`, `display_order` FROM `home_sections` ORDER BY `display_order`;
