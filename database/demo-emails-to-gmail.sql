-- Move the demo CUSTOMER and VIP MEMBER logins to Gmail addresses on an existing database.
-- Staff logins (Super Admin, Admin, Sub Admins, Branch Manager, Commission Partners) stay on @globallife.co.in.
-- Matches both the old @globallife.in and the @globallife.co.in address, so it works whether or not
-- rename-email-domain.sql was run first. Passwords are unchanged. Safe to run more than once.
SET NAMES utf8mb4;
START TRANSACTION;

UPDATE `users` SET `email` = 'globallife.vipdemo@gmail.com'
 WHERE `email` IN ('vip-member@globallife.co.in', 'vip-member@globallife.in');

UPDATE `users` SET `email` = 'lifelinehospital.globallife@gmail.com'
 WHERE `email` IN ('hospital@globallife.co.in', 'hospital@globallife.in');

UPDATE `users` SET `email` = 'rangoliboutique.globallife@gmail.com'
 WHERE `email` IN ('boutique@globallife.co.in', 'boutique@globallife.in');

UPDATE `users` SET `email` = 'gitanjalistore.globallife@gmail.com'
 WHERE `email` IN ('gitanjali@globallife.co.in', 'gitanjali@globallife.in');

UPDATE `users` SET `email` = 'amitpatel.globallife@gmail.com'
 WHERE `email` IN ('patient.demo@globallife.co.in', 'patient.demo@globallife.in');

-- The demo customer's email is also copied onto their orders.
UPDATE `orders` SET `customer_email` = 'amitpatel.globallife@gmail.com'
 WHERE `customer_email` IN ('patient.demo@globallife.co.in', 'patient.demo@globallife.in');

COMMIT;

-- Check
SELECT `id`, `name`, `email` FROM `users` WHERE `email` LIKE '%.globallife@gmail.com' OR `email` = 'globallife.vipdemo@gmail.com';
