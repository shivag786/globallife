-- ============================================================
-- Global Life — Razorpay payment gateway
-- Equivalent of: php artisan migrate
--   2026_08_20_000001_add_payment_gateway_fields_to_orders_table
--
-- Use this only when you cannot run artisan on the server
-- (e.g. phpMyAdmin on shared hosting). Run STEP 1 first.
-- ============================================================


-- ------------------------------------------------------------
-- STEP 1 — CHECK WHAT IS MISSING (run this alone, first)
-- ------------------------------------------------------------
-- Which of the recent migrations has this server already applied?
SELECT migration, batch
FROM migrations
WHERE migration IN (
  '2026_07_30_000001_extend_home_sections_type_enum_v5',
  '2026_07_31_000001_extend_home_sections_type_enum_v6',
  '2026_08_01_000001_add_delivery_tracking_to_orders_table',
  '2026_08_01_000002_create_addresses_table',
  '2026_08_20_000001_add_payment_gateway_fields_to_orders_table'
)
ORDER BY batch, migration;

-- Anything NOT listed in the result is missing. Run only the
-- matching section below. Sections 3-6 are almost certainly
-- already applied if orders are working today — do not run them blindly.


-- ------------------------------------------------------------
-- STEP 2 — PAYMENT GATEWAY  (the new feature; run this)
-- ------------------------------------------------------------
ALTER TABLE `orders`
  ADD COLUMN `payment_gateway`     varchar(30)  NULL DEFAULT NULL AFTER `payment_method`,
  ADD COLUMN `razorpay_order_id`   varchar(255) NULL DEFAULT NULL AFTER `payment_gateway`,
  ADD COLUMN `razorpay_payment_id` varchar(255) NULL DEFAULT NULL AFTER `razorpay_order_id`,
  ADD COLUMN `razorpay_signature`  varchar(255) NULL DEFAULT NULL AFTER `razorpay_payment_id`,
  ADD INDEX `orders_razorpay_order_id_index`   (`razorpay_order_id`),
  ADD INDEX `orders_razorpay_payment_id_index` (`razorpay_payment_id`);

-- Tell Laravel this migration is done, so a later `artisan migrate`
-- does not try to add these columns again and fail.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_20_000001_add_payment_gateway_fields_to_orders_table',
       COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`;


-- ------------------------------------------------------------
-- STEP 3 — Payment defaults in settings (optional but recommended)
-- ------------------------------------------------------------
-- Razorpay stays OFF until you add the keys in the admin panel.
-- COD stays ON so checkout keeps working meanwhile.
-- The key id / secret are NOT set here — add them at
-- /admin/settings/payment so the secret gets encrypted properly.
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
  ('razorpay_enabled',   '0',     NOW(), NOW()),
  ('razorpay_mode',      'test',  NOW(), NOW()),
  ('razorpay_currency',  'INR',   NOW(), NOW()),
  ('cod_enabled',        '1',     NOW(), NOW()),
  ('payment_test_mode',  '0',     NOW(), NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;   -- keeps any value you already set


-- ============================================================
-- BELOW: only if STEP 1 showed these as missing
-- ============================================================


-- ------------------------------------------------------------
-- STEP 4 — Address book  (checkout REQUIRES this table)
-- ------------------------------------------------------------
CREATE TABLE `addresses` (
  `id`         bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id`    bigint(20) unsigned NOT NULL,
  `name`       varchar(255) NOT NULL,
  `phone`      varchar(30)  NOT NULL,
  `address`    text         NOT NULL,
  `city`       varchar(120) NOT NULL,
  `state`      varchar(120) NOT NULL,
  `pincode`    varchar(12)  NOT NULL,
  `is_default` tinyint(1)   NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_is_default_index` (`user_id`, `is_default`),
  CONSTRAINT `addresses_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_01_000002_create_addresses_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`;


-- ------------------------------------------------------------
-- STEP 5 — Delivery tracking columns on orders
-- ------------------------------------------------------------
ALTER TABLE `orders`
  ADD COLUMN `expected_delivery_date` date      NULL DEFAULT NULL AFTER `placed_at`,
  ADD COLUMN `processing_at`          timestamp NULL DEFAULT NULL AFTER `expected_delivery_date`,
  ADD COLUMN `dispatched_at`          timestamp NULL DEFAULT NULL AFTER `processing_at`,
  ADD COLUMN `delivered_at`           timestamp NULL DEFAULT NULL AFTER `dispatched_at`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_01_000001_add_delivery_tracking_to_orders_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`;


-- ------------------------------------------------------------
-- STEP 6 — Homepage section types (v5 + v6)
-- ------------------------------------------------------------
ALTER TABLE `home_sections`
  MODIFY COLUMN `type` enum(
    'hero','about','features','stats','cta','founder_quote',
    'products_showcase','testimonials_showcase','presence_map',
    'business_opportunity','process_steps','vip_plans','blog_showcase',
    'upcoming_events','gallery','enquiry_form','team','certifications',
    'quality','category_products','products_by_category'
  ) NOT NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_30_000001_extend_home_sections_type_enum_v5', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_31_000001_extend_home_sections_type_enum_v6', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`;


-- ------------------------------------------------------------
-- STEP 7 — Verify
-- ------------------------------------------------------------
SHOW COLUMNS FROM `orders` LIKE 'razorpay%';
SHOW COLUMNS FROM `orders` LIKE 'payment_gateway';
-- Expect 3 rows from the first (order_id, payment_id, signature)
-- and 1 row from the second.

-- After importing, clear the caches on the server:
--   php artisan config:clear && php artisan view:clear
-- or delete bootstrap/cache/*.php and storage/framework/views/*.php
