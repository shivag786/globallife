-- Move every @globallife.in email address to @globallife.co.in on an EXISTING database.
-- Safe to run more than once: rows already on @globallife.co.in are not matched again.
-- Passwords are unchanged; users simply log in with the new address.
-- Also update SUPER_ADMIN_EMAIL in the server's .env to superadmin@globallife.co.in.
SET NAMES utf8mb4;
START TRANSACTION;

-- Login accounts (Super Admin, Admin, Sub Admins, Branch Managers, Partners, VIP Members, demo customer).
UPDATE `users`
   SET `email` = REPLACE(`email`, '@globallife.in', '@globallife.co.in')
 WHERE `email` LIKE '%@globallife.in';

-- Email copied onto past orders (demo customer).
UPDATE `orders`
   SET `customer_email` = REPLACE(`customer_email`, '@globallife.in', '@globallife.co.in')
 WHERE `customer_email` LIKE '%@globallife.in';

-- Site settings, e.g. the contact email shown on the website (cmd@globallife.in).
UPDATE `settings`
   SET `value` = REPLACE(`value`, '@globallife.in', '@globallife.co.in')
 WHERE `value` LIKE '%@globallife.in%';

-- Business email on any VIP business page that used a company address.
UPDATE `vip_microsites`
   SET `business_email` = REPLACE(`business_email`, '@globallife.in', '@globallife.co.in')
 WHERE `business_email` LIKE '%@globallife.in';

COMMIT;

-- Check: should return no rows.
SELECT `id`, `email` FROM `users` WHERE `email` LIKE '%@globallife.in';
