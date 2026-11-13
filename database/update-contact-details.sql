-- Website contact details: new WhatsApp number and the two footer emails.
-- Safe to run more than once. Editable later in Admin → Site Settings → Contact.
SET NAMES utf8mb4;

-- WhatsApp: footer, floating WhatsApp button, Contact page and product pages.
-- Keep the +91: the wa.me link is built from these digits and needs the country code.
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES ('contact_whatsapp', '+91 88699 11138', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();

-- Footer emails: Contact Email + HR Email.
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES ('contact_email', 'info@globallife.co.in', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES ('contact_email_hr', 'hr@globallife.co.in', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();

-- Settings are cached forever; drop the cached copy so the site shows the new values.
-- (Only works with CACHE_STORE=database. Otherwise run: php artisan cache:clear,
--  or open Admin → Site Settings and press Save once.)
DELETE FROM `cache` WHERE `key` LIKE '%site-settings';

-- Check
SELECT `key`, `value` FROM `settings` WHERE `key` IN ('contact_whatsapp', 'contact_email', 'contact_email_hr');
