# Global Life — Project Details

> Living document. **Keep this file updated** whenever roles, routes, models, the
> commission engine, or major flows change. Last updated: 2026-07-25.

## 1. What this project is

**Global Life** is a Laravel 13 multi-tenant platform that combines:

1. **A public marketing/CMS website** — a homepage built from admin-editable
   "Home Sections", plus blog, events, testimonials, VIP plans, contact/enquiry.
2. **A network-marketing / MLM-style hierarchy** — Branch Managers create
   Commission Partners, who create VIP Members. Each level earns commission.
3. **VIP Member microsites** — every VIP member gets a public business profile
   page (a mini-website) at `/{city}/{business}/{secureId}` with banners,
   services, products, gallery, videos, FAQs, reviews, and lead capture.
4. **An e-commerce storefront** — a Super-Admin product catalog that VIP members
   resell on their microsites. Orders drive a database-driven **product
   commission split** paid up the seller's upline into wallets.

The word "manager" in the URL prefixes is historical: the `/manager` prefix
belongs to the **Commission Partner** role (renamed from "city_manager").

## 2. Tech stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel `^13.8`, PHP `^8.3` |
| Auth | Laravel Fortify `^1.36` (login, 2FA, password reset) |
| RBAC | spatie/laravel-permission `^8.1` (roles + permissions, `web` guard) |
| Audit | spatie/laravel-activitylog `^4.12` |
| Frontend build | Vite `^8`, Tailwind CSS `^4`, Bootstrap `^5.3`, Sass |
| JS libs | ApexCharts, GSAP, Motion, SweetAlert2, Three.js |
| Testing | PHPUnit `^12.5` (Feature tests under `tests/Feature`) |
| DB | MySQL/MariaDB (XAMPP local dev) |
| Dev env | Windows 11 + XAMPP, project at `c:\xampp\htdocs\global_life_new` |

Model attributes use **PHP 8 attributes** (`#[Fillable([...])]`, `#[Hidden([...])]`)
instead of `$fillable`/`$hidden` properties — a Laravel 13 pattern used throughout.

Build note: `npm run build` first runs `scripts/build-bootstrap.mjs`
(`build:bootstrap`) before `vite build`.

## 3. Roles & RBAC

Roles are seeded by `RolesAndPermissionsSeeder` (const `ROLES`). Eight roles:

| Role | Prefix / area | How created | Notes |
|------|---------------|-------------|-------|
| `super_admin` | `/admin` | Seeded from `.env` (`SUPER_ADMIN_*`) | **Gate::before bypass** — passes every gate (`AppServiceProvider`) |
| `admin` | `/admin` | Seeded (`admin@globallife.in`) | Has ALL content-module permissions by default |
| `sub_admin` | `/admin` | Created by Super Admin | Starts with NO permissions; granted per-module via matrix |
| `branch_manager` | `/branch` | Created by Super Admin | Assigned cities; granted `branch.*` perms per-user |
| `commission_partner` | `/manager` | Created by Branch Manager | Serves cities; creates VIP members |
| `vip_member` | `/vip` | Created by Commission Partner | Owns a public microsite + storefront |
| `customer` | `/account` | Registers or logs in at checkout | Order history + saved addresses |
| `visitor` | — | Guest | Unauthenticated |

**Permission matrices** (direct-to-user perms for sub_admin/branch_manager, not role-level):
- `PermissionMatrixService` — Sub Admin content modules:
  `blog, products, leads, events, media, testimonials` × actions
  `create, edit, delete, publish, approve, export` → `"{module}.{action}"`.
- `BranchPermissionMatrixService` — Branch Manager `branch.*` permissions.
- Super Admin bypasses the matrix entirely via `Gate::before`.
- Admin gets `PermissionMatrixService::allPermissions()` synced to the role.

`EnsureAccountIsActive` middleware (alias `active_account`) logs out + blocks any
user whose `status !== 'active'` (Super-Admin block feature).

Dashboard routing: `DashboardController@index` redirects each role to its panel
dashboard (admin/branch/manager/vip) or, for customers, to `account.orders`.

## 4. Domain models (`app/Models`)

**Users & hierarchy**
- `User` — hierarchy via self-referential `created_by` (`creator` / `commissionPartners` / `vipMembers`). Relations: `cities` (commission_partner, `city_manager` pivot w/ commission), `branchCities` (branch_manager, `branch_manager_cities` pivot), `vipMicrosite`, `wallet`, `orders`, `commissionEarnings` (as beneficiary), `leads`. Has `commission_percentage`, `status`, `mobile`.
- `City` — has slug; managers/branches attached via pivots.

**CMS / marketing**
- `HomeSection` (ordered, toggleable, typed enum — extended several times), `BlogPost`, `BlogComment`, `BlogLike`, `Testimonial`, `Event`, `MediaItem`, `Lead`, `Setting`, `VipPlan`.

**VIP microsite (public business profile)**
- `VipMicrosite` — the core tenant object. `module_visibility` JSON, business
  profile fields, `publicPath()` builds `/{city}/{slug}/{userId}-{token}-{creatorId}`,
  `completionPercentage()`, `isModuleVisible()`. Owns: `banners`, `services`,
  `products` (free-text), `catalogProducts` (Super-Admin products resold via
  `vip_products` pivot), `galleryItems`, `videos`, `faqs`, `reviews`, `events`
  (`BusinessProfileEvent` = click analytics), `commissionTransaction`.
- `Business*` models: `BusinessBanner/Service/Product/GalleryItem/Video/Faq/Review/ProfileEvent`.
- `CommissionTransaction` — VIP-activation (joining) commission, one per microsite.

**E-commerce catalog & orders**
- `Product` — catalog item; `Category`, `Brand`, `ProductBenefit`. Has pricing
  (`sellingPrice()`, `hasPrice()`), `status`, `is_featured`, `display_order`, `sku`.
- `CommissionRule` — product-sale commission config; scope = `product|category|global`, `role`, `type` (percent|fixed), `value`, active flag.
- `Order` / `OrderItem` — `STATUS_FLOW = pending→confirmed→processing→dispatched→delivered`; route key = `order_number`; `commission_credited` flag.
- `Wallet` — per-user balance; `CommissionEarning` — one row per beneficiary per order item (pending → approved on delivery).
- `CommissionPayout` — a super-admin settlement of one beneficiary's earnings for one calendar month (`period` = `YYYY-MM`), splitting `product_amount` / `vip_amount` and recording `wallet_debited`.

## 5. Two SEPARATE commission systems

Do not conflate these:

1. **VIP-activation (joining) commission** — `CommissionTransaction`, created
   when a VIP microsite is activated (`VipActivationService`). One transaction
   per microsite, split among the upline based on `commission_percentage` / city
   pivot values.

2. **Product-sale commission** — `ProductCommissionService` +
   `CommissionRule` + `CommissionEarning` + `Wallet`. Fully DB-driven, no
   hardcoded levels. Key logic:
   - `resolveRule(product, role)` — most-specific rule wins:
     product → category → global.
   - Earning roles: `vip_member`, `commission_partner`, `branch_manager`
     (`EARNING_ROLES`). **Company (super_admin) always takes the remainder.**
   - `resolveBeneficiaries(seller)` walks the seller's `created_by` upline
     (guarded to 12 hops) to find who holds each role.
   - Amounts clamped: percent 0–100, never exceeds the line base.
   - At order time earnings are **pending**; on `markDelivered()` they flip to
     **approved** and credit each `Wallet` exactly once (guarded by
     `commission_credited`).

**Paying a partner out** (`PartnerPayoutService`, super-admin only) is the one
place that *reads* both systems — it still never merges them. Per Commission
Partner per month:

- month bucket = `CommissionEarning.created_at` (order date) and
  `CommissionTransaction.activated_at`, so each row belongs to exactly one month;
- `payable = (approved product earnings + VIP partner amounts in the month)
  − (already paid out for that month)`; pending earnings are excluded;
- `markPaid()` writes a `CommissionPayout` and decrements the wallet by the
  **product share only** (the VIP ledger never credited the wallet), never below
  zero, under a `lockForUpdate()` on the wallet row;
- a month can be settled more than once — a late delivery approves more earnings
  inside an already-paid month and the delta becomes payable again.

## 6. E-commerce flow

- **Cart** (`CartService`) — session-backed, guest-friendly. Stores only
  product_id + seller_id (VIP microsite) + qty; **all prices re-looked-up from DB**
  at read time (session can't influence price). Free shipping ≥ ₹999, else ₹49
  flat. Max qty 99.
- **Wishlist** (`WishlistService`), **Checkout** (`CheckoutController` +
  `OrderService`).
- **Payment methods are admin-driven** — `PaymentGatewayService` reads the
  `settings` table and decides which `payment_choice` values checkout may render
  *and* accept (`options()` / `allowedChoices()`); it never returns an empty list
  (COD is the fallback). Admin edits them at **`/admin/settings/payment`**
  (`Admin\PaymentSettingsController`, super_admin only): Razorpay on/off, mode
  (test/live), Key ID, Key Secret, currency, plus Cash-on-Delivery and the
  simulated test-payment toggles. The **Key Secret is encrypted at rest**
  (`Crypt`) — the only setting that is — and is never sent back to the browser
  (blank field = keep the stored one). A "Test connection" action pings the
  Razorpay API with the saved keys.
- **Razorpay checkout** (`RazorpayService` + `RazorpayCheckoutController`) — no
  SDK, straight REST over `Http` (`api.razorpay.com/v1`). Two AJAX steps:
  `POST /checkout/razorpay/create` prices the **cart server-side**, opens a
  Razorpay order for that amount and writes a **`pending_orders` snapshot**
  (address + priced line items); Razorpay Checkout collects the money in the
  browser; `POST /checkout/razorpay/verify` re-checks the HMAC-SHA256 of
  `"<order_id>|<payment_id>"` and hands off to `RazorpayPaymentConfirmer`. The
  amount is **never** read from the request. Orders store `payment_gateway` +
  `razorpay_order_id/payment_id/signature`. Selecting Razorpay without JS is
  rejected with a message rather than silently posting.
- **Why `pending_orders` and not the session:** the webhook is server-to-server
  and has no session, so the cart would be invisible to it. Everything the order
  needs is snapshotted at create time. On confirmation the **stored** name / sku
  / price win — the customer already paid them, so a product edited, deactivated
  or deleted in the meantime must not change or drop a paid line
  (`OrderService::rehydrateSnapshot()`); the live models are loaded only to price
  commission, which is skipped if the product is gone.
- **Razorpay webhook** (`RazorpayWebhookController`, `POST /webhooks/razorpay`) —
  the safety net for a payment whose browser never returned (tab closed, signal
  lost). CSRF-exempt via `validateCsrfTokens(except: ['webhooks/*'])` in
  `bootstrap/app.php`; authenticated instead by HMAC-SHA256 of the **raw body**
  against the **webhook secret** (a different value from the API key secret,
  `razorpay_webhook_secret`, also encrypted at rest). Handles
  `payment.captured` / `order.paid` and `payment.failed`; anything else — and any
  unknown order — is **acknowledged with 2xx** so Razorpay stops retrying and
  does not disable the endpoint.
- **`RazorpayPaymentConfirmer`** is the single confirmation path shared by the
  browser and the webhook. It locks the `pending_orders` row `FOR UPDATE`, so the
  two racing callers cannot both pass the status check: the first creates the
  order, the second gets the same one back. Mail is sent **outside** the
  transaction so a slow SMTP server never holds the lock and a bounced email
  never rolls back a paid order.
- **`PaymentSimulator`** still backs the `online_success | online_fail | cod`
  paths; `OrderService` skips it when `payment_gateway === 'razorpay'` because
  the money is already captured and verified by then.
- **Checkout requires an account** (single progressive page). Guests hit an
  **identify step** (`partials/checkout/identify`): *register* — set their own
  password → `customer` account + login (`CheckoutController@register`), or *log
  in* (`@login`). Both submit via progressive AJAX (reload into the authenticated
  step) and still work without JS. Authenticated buyers pick a **saved delivery
  address** (address book) and pay (`partials/checkout/authenticated`).
  `OrderService::placeFromCart(array, User)` requires the authenticated user and a
  chosen address (snapshotted onto the order) — the old silent guest/email-attach
  path is gone.
- **Address book** — `Address` model + `addresses` table (one `is_default` per
  user), managed at `/account/addresses` (`Account\AddressController`: store /
  set-default / destroy) and inline at checkout.
- **`StorefrontContext`** — keeps VIP-store branding across cart/checkout/order
  and points "Continue Shopping" back to the VIP microsite when all cart items
  share one seller.
- **Direct storefront shopping** — the same AJAX card (`x-store-product-card`)
  and cart machinery serve non-VIP sales too: rendered with **no seller**, so
  `OrderService` records **zero commission** (its earnings step returns early
  without a microsite). Browse model: the homepage `category_products` CMS
  section (`ProductRepository::activeCategoriesWithProducts()`) shows **category
  image tiles**, each linking to `/products/category/{slug}`
  (`ProductController@category`) which lists that category's products with
  add-to-cart. A second section, `products_by_category`
  (`ProductRepository::groupedByCategory()`), renders **product cards grouped by
  category** inline on the homepage. The all-products page (`/products`) uses the
  same add-to-cart card. All direct purchase, no upline. Homepage order:
  hero → `category_products` tiles → `products_by_category` grid → rest.
- Routes: `/cart`, `/wishlist`, `/checkout`, `/checkout/confirmation/{order}`,
  `/account/orders`. Admin order mgmt at `/admin/orders` (status updates credit
  commission on delivery).
- **New-order sound alert** — super_admin/admin panels poll `admin.orders.poll`
  (JSON: `latest_id` + `new` since a given id) every 20s and play a synthesized
  chime + toast + sidebar badge on new orders (`partials/admin-order-alert`,
  self-contained inline JS; audio unlocked on first user gesture).
- **Delivery promise + tracking** — `DeliveryService` reads a global
  `delivery_days` setting (default 5, edited in Site Settings → Delivery). Product
  pages show "Order today, delivery by <today + days>". At checkout each order's
  `expected_delivery_date` defaults to `placed_at + days`; admins override it per
  order (`admin.orders.update-delivery`). Status changes stamp milestone
  timestamps (`processing_at`/`dispatched_at`/`delivered_at`) that drive an
  Amazon/Flipkart-style tracking timeline (`x-order-tracker`) shown to customers
  and (read-only) admins. **All timestamps stored UTC, displayed in IST** via the
  `x-ist` component (`Asia/Kolkata`); app timezone stays UTC.

## 7. Route map (high level) — `routes/web.php`

- **Public**: `/`, `/experience`, `/third`, `/fourth`, `/scooter`, `/vip-plans`,
  `/products`, `/products/category/{slug}`, `/blog`, `/events`, `/contact`,
  cart/wishlist/checkout.
- **Authed (`auth` + `active_account`)**: `/dashboard` (role redirect), `/wallet`,
  `/account/orders`, `/account/addresses`.
  - `/admin/*` (`super_admin|admin|sub_admin`) — products, blog, testimonials,
    events, media, leads, orders. **Super-Admin-only** sub-group: cities,
    categories, brands, commissions, branch-managers, commission-partners,
    partner-payouts (monthly settlement + mark paid), vip-members, activity-logs,
    revenue, vip-plans, home-sections, settings,
    settings/payment (payment gateway).
  - `/manager/*` (`commission_partner`) — dashboard, leads, vip-members, revenue.
  - `/branch/*` (`branch_manager`) — dashboard, commission-partners, revenue.
  - `/vip/*` (`vip_member`) — dashboard, profile, module visibility, banners,
    services, products, marketplace, gallery, videos, faqs, reviews, leads.
- **Microsite (registered last, tight regex constraints so it never shadows other
  routes)**: `/{citySlug}/{businessSlug}/{secureId}` (`microsite.show`),
  review POST, and `/microsite/{id}/click/{type}` analytics redirect.

## 8. Architecture patterns

- **Repositories** (`app/Repositories`) for read queries: `HomeSection`, `BlogPost`,
  `VipPlan`, `Product`, `Lead`, `City`, `Testimonial`, `Event`, `Media`.
- **Services** (`app/Services`) for business logic: commission, cart, wishlist,
  order, storefront, permission matrices, VIP/branch/partner management,
  activation, payment (`PaymentGatewayService`, `RazorpayService`,
  `PaymentSimulator`), settings.
- **Support** (`app/Support`): `BusinessModules` (microsite module keys/defaults),
  `ChartData`.
- **Blade views**: `partials/home-sections/*` (CMS blocks), `partials/microsite/*`
  (microsite modules), `dashboards/*` (per-role), `admin/*`, `vip/*`, `branch/*`,
  `manager/*`, reusable `components/*`.

## 9. Seeded accounts (dev)

Password for all demo accounts: `Password!123` (Super Admin from `.env`).

| Email | Role |
|-------|------|
| `.env SUPER_ADMIN_EMAIL` | super_admin |
| `admin@globallife.in` | admin |
| `blog-subadmin@globallife.in` | sub_admin (blog only) |
| `branch.manager@globallife.in` | branch_manager (Jhansi, Delhi) |
| `jhansi.managerA/B@`, `delhi.managerA@globallife.in` | commission_partner |
| `vip-member@globallife.in` | vip_member |

Seeder order: Roles → Cities → VipPlans → Users → Products → Blog → Testimonials
→ Events → Settings → HomeSections → Leads.

## 10. Tests (`tests/Feature`)

`CommissionSplitTest`, `ProductCommissionTest`, `OrderCommissionTest`,
`RevenueVisibilityTest`, `RbacBoundariesTest`, `ReviewModerationTest`,
`CartAjaxTest`, `AdminDashboardOverviewTest`, `AdminVipMembersTest`,
`PanelDashboardsRenderTest`, `CheckoutAuthTest`, `DeliveryTrackingTest`,
`RazorpayWebhookTest` (signature rejection, snapshot->order, redelivery
idempotency, webhook/browser race, failed payments),
`PaymentGatewaySettingsTest` (Razorpay settings, option gating, signature
verification), `PartnerPayoutTest` (monthly product+VIP report, mark paid clears
withdrawable but not pending, re-settling a month, month isolation, RBAC).
Helper: `tests/Support/BuildsCommissionChain`.
Run: `php artisan test` (or `composer test`).

## 11. Current branch / work in progress

Branch: `feature/ajax-cart-storefront-context`. Recent work (per git status):
- AJAX cart flow + VIP storefront context.
- New `Admin/VipMemberController` + `admin/vip-members/` views.
- New reusable components: `revenue-flow`, `stat-tile`.
- New landing pages: `third.blade.php`, `fourth.blade.php`.
- Dashboard overhauls (admin, branch-manager, commission-partner, vip-member).

## 12. Dev commands

```bash
composer setup      # install, key gen, migrate, npm install + build
composer dev        # serve + queue + pail logs + vite (concurrently)
composer test       # config:clear + artisan test
php artisan migrate --seed
npm run build       # build-bootstrap + vite build
```

---

## Suggested additional docs (optional)

These would complement this file if you want them — say the word:
- **`CLAUDE.md`** — a short version of §2/§3/§8 so any AI assistant loads project
  conventions automatically (none exists yet; recommended).
- **`docs/commission.md`** — worked numeric examples of both commission systems.
- **`docs/erd.md`** or a diagram — the model relationships in §4/§5.
- **`.env.example` review** — confirm all required keys (SUPER_ADMIN_*, mail) are
  documented for onboarding.
