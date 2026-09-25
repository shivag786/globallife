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
- `VipRenewal` — audit trail of a Commission Partner's approve/reject decision on an expired plan.
- `WithdrawalRequest` — a VIP member asking to withdraw their wallet; `pending` until an admin records the manual transfer's UTR / screenshot, which is when the wallet is debited.
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

## 5b. VIP plan validity & renewal

- `vip_plans.validity_months` (default 12) is the length of one paid cycle.
- `VipActivationService::activate()` stamps `vip_microsites.plan_expires_at =
  now() + validity_months`. **A microsite that was never activated has a NULL
  expiry and is NOT expired** — it behaves exactly as before.
- Expired = `plan_expires_at` is set and in the past (`hasExpiredPlan()`).
- **Public effect of expiry**: `MicrositeController` serves
  `microsite.maintenance` with **HTTP 503** instead of the profile, records no
  `page_view`, and blocks review submission; `MicrositeClickController` stops
  redirecting/recording contact clicks. The VIP member's own dashboard shows a
  banner explaining why.
- **Renewal** (`VipRenewalService`, Commission Partner only, own members only):
  Approve/Reject buttons appear on `/manager/vip-members` **only when the plan
  has expired**. Approve restarts the cycle from *today* (`now() +
  validity_months`) and the page goes live again; Reject only logs the refusal
  and the page stays on the maintenance notice. Both write a `VipRenewal` row.
  Renewing a plan that has not expired is refused.
- Payment is offline, confirmed by the partner — same as first activation.
  **Renewal records NO commission**; `CommissionTransaction` remains
  one-per-microsite and joining-only.

**The four packages** (seeded by migration `2026_09_21_000003` and
`VipPlanSeeder`). The legacy Silver/Gold/Platinum/Diamond rows were **deleted**
by `2026_09_22_000002`; there is no admin screen to add or edit plans, so these
four are fixed in code and changing one needs a migration:

| Package | Price | Validity | Products | Services |
|---------|-------|----------|----------|----------|
| Growth | ₹4,999 | 3 months | 15 | 6 |
| Professional | ₹14,999 | 6 months | 35 | 15 |
| Growth Plus | ₹24,999 | 1 year | 50 | 30 |
| Premium | ₹41,999 | 1 year | 100 | 100 |

`vip_plans.product_limit` / `service_limit` cap the VIP's OWN content —
`business_products` (`/vip/products`) and `business_services`
(`/vip/services`). The marketplace (`catalogProducts`) is not capped.

The cap is on the **total row count**, so items created under a bigger package
still count afterwards: 15 products on Growth (15) means no 16th, while
Professional (35) leaves 20 free slots. Read it via
`VipMicrosite::contentQuota('products'|'services')`, which returns
`used/limit/remaining/can_add/over`. Enforcement is in the `quotaBlock()` guard
on `Vip\ProductController` and `Vip\ServiceController` — on both `create()` and
`store()`, so a direct POST cannot slip past it. **A downgrade never deletes or
hides anything**: the member keeps what they have and simply cannot add more.

Approving a renewal switches `vip_microsites.vip_plan_id` to the chosen package,
which is what moves the member's caps, and sets the new expiry from *today*.

## 5c. VIP member withdrawals

`WithdrawalService` — **VIP members only**. Commission Partners and Branch
Managers are settled monthly by the Super Admin via `admin/partner-payouts`
instead; same wallet, two deliberately separate mechanisms.

- `MINIMUM_AMOUNT = 500`, `COOLDOWN_HOURS = 24` — one request per 24 hours.
- `availableBalance()` = wallet minus any pending request, so two open requests
  can never together exceed the wallet.
- **The wallet is debited on mark-paid, not on request**, so a pending request
  never makes money vanish from the member's view.
- `blockReason()` is checked **before** the amount is validated, because the
  Request button is deliberately always enabled: pressing it must report "already
  available, kindly try after 24 hours" rather than complain about the figure.
  `hasOpenRequest()` decides whether the form stays on screen at all — it does
  while a request is open, but not when the balance is simply below ₹500.
- `markPaid()` requires a UTR number **or** a screenshot (`required_without`
  both ways, plus a service-level guard). There is no payment gateway: the admin
  transfers by hand and records proof, which is what the member is shown.
- Member side (`/wallet`): request form + one card per request showing amount and
  status. **View Detail appears only once paid**, opening a `<dialog>` with the
  UTR, note and screenshot.
- Admin side (`admin/withdrawals`, super-admin only): table of who asked, for how
  much, wallet now vs at request, with a Mark as Paid `<dialog>` carrying the
  proof form. The admin dashboard has a "Withdrawal Requests from VIP Members"
  box with the pending count that links straight to the queue.

### Deleting the legacy plans (2026-09-22)

The pre-package plans are gone, which required two concessions:

- `vip_microsites.vip_plan_id` is NOT NULL, so microsites still on a legacy plan
  were **repointed** to the equivalent package (silver→growth, gold→professional,
  platinum→growth-plus, diamond→premium). That also gave them real caps: the
  legacy rows carried `product_limit = 0`, which had been blocking those members
  from adding any content.
- `commission_transactions.vip_plan_id` and `vip_renewals.vip_plan_id` are now
  **nullable with ON DELETE SET NULL**. The money facts — `package_amount`, the
  percentages and every party's share — survive untouched, but those historical
  rows no longer name the package that was sold. Anything reading a plan off
  *history* must use `?->`; a microsite's own `vipPlan` is still never null.

The admin VIP Plans CRUD (controller, form requests, views, routes, sidebar link)
was removed at the same time. The dashboard still counts active plans but no
longer links anywhere.

### City picker (state -> city, with inline creation)

`<x-city-picker>` has two modes. **Multi** (chips) is used by the Branch Manager
form; **single** (`single` prop) is used when adding a VIP Member, which needs
exactly one city. Both give a **state** dropdown, a **city** dropdown filtered to
that state, and "Other" on either one revealing a text box so a missing city — or
a state with no cities yet — can be typed in.

- `CityDirectoryService` supplies `states()` and `citiesByState()` for the
  cascade, and `resolveOrCreate($name, $state)` for typed entries. Matching is
  case-insensitive on name + state, so "  jhansi " reuses the existing Jhansi
  rather than duplicating it.
- **`cities.slug` is globally unique** because it is the first segment of a
  microsite URL, so a second "Springfield" in another state gets
  `springfield-bihar`, then a numeric suffix if that is taken too.
- **Territory follows the work.** Neither a Commission Partner nor a Branch
  Manager is handed cities on a form any more: `CityWithinCommissionPartner` and
  `CityWithinBranchManager` are both gone. Registering a VIP Member is what
  grants the city — `VipMemberService::createMember()` takes an already-resolved
  `City` and `syncWithoutDetaching`s it onto the partner's `cities` **and** their
  Branch Manager's `branchCities`.
- Single mode posts `city_id`, or `new_city[name|state]` when "Other" is chosen;
  its "Other" option carries `value=""` so a no-JS submit posts an empty id
  rather than a bad one. `StoreVipMemberRequest::prepareForValidation()` resolves
  a typed city that already exists to its id, so the per-city business-name check
  still works; a genuinely new city is created in the service instead, rather
  than stranding a row if validation then fails.
- The picker JS is code-split (`resources/js/forms/city-picker.js`), loaded only
  when `[data-city-picker]` is present. Without JS the chips still post
  correctly, so a failed submit never loses what was entered.

### Admin-set passwords

`PUT admin/branch-managers/{branchManager}/password` is the **only** place in the
app where one account can set another's password, and its form lives solely on
the Branch Manager edit screen (its own `<form>`, a sibling of the profile form —
nesting forms is invalid HTML, and a reset should not ride along with a save).

- Super-admin only, via `UpdateBranchManagerPasswordRequest`, and the controller
  additionally `abort_unless($branchManager->hasRole('branch_manager'), 404)` so
  the route's `User` binding cannot be used to reset an arbitrary account.
- `password` needs `confirmed` — an admin typing someone else's password gets no
  login attempt to catch a typo.
- `BranchManagerService::setPassword()` also **drops every `sessions` row for that
  user and rotates `remember_token`**, so a reset genuinely cuts off the old
  credentials rather than leaving a live browser working.
- `User::getActivitylogOptions()` uses `logOnly([...])` without `password`, so no
  hash reaches `activity_log`.

The Branch Manager has the same power one level down:
`PUT branch/commission-partners/{commissionPartner}/password`, its form living
only on the partner edit screen. `UpdateCommissionPartnerPasswordRequest`
requires the `branch_manager` role **and** `created_by === $user->id`, so a
manager can only reset a partner they created, and the controller `abort_unless`
the target holds `commission_partner`. `CommissionPartnerService::setPassword()`
mirrors the Branch Manager one — sessions dropped, remember-me token rotated.

These two screens are the only places in the app where one account can set
another's password.

### Mobile numbers

`App\Support\MobileNumber::normalise()` reduces any input to its **last 10
digits**, so `+91 98765 43210`, `09876543210` and `+91-98765-43210` all store as
`9876543210` — taking the tail handles every country code and trunk prefix
without needing a list of prefixes to strip. Shorter input is returned as-is so
validation reports it rather than silently accepting a wrong number.

Every request that writes `users.mobile` calls it from `prepareForValidation()`,
then validates `digits:10` + `Rule::unique('users','mobile')`: both branch
manager requests **and** `Vip\UpdateProfileRequest`, which writes the same
column — the rule has to hold there too or it means nothing.

`resources/js/forms/mobile-input.js` (`[data-mobile-input]`) mirrors this in the
browser with one deliberate difference: **bulk input takes the last 10 digits,
typing takes the first 10**. Taking the tail while someone types would drop the
digits they entered first. It listens to `input`, `change` and `blur`, because
autofill often fires only `change`.

### Form and permission UI conventions

- **Input borders.** `--color-input-border` in `resources/css/app.css` is the one
  dial for every form control's border. The rule is **deliberately unlayered**
  (placed after every `@layer` block): a rule inside `@layer base` loses to
  Tailwind's utilities layer whatever its specificity, so `border-slate-300`
  would win. It sets `border-width` and `border-style` too, not just the colour —
  Preflight sets `border: 0 solid` and this project has no Tailwind forms plugin,
  so those utility classes had only been colouring a zero-width border and most
  inputs rendered with no border at all. Focus colour is restated there for the
  same cascade reason.
- **Autofocus.** `resources/js/app.js` marks the first usable field of every form
  with `autofocus` and focuses the first one on the page. It skips disabled,
  readonly, hidden and `display:none` fields, anything inside a closed
  `<dialog>`, and uses `preventScroll` so the page does not jump.
- **Permission grids.** `<form data-permission-matrix>` picks up
  `resources/js/forms/permission-matrix.js`, giving select-all, per-row (module)
  and per-column (action) toggles. The toggles carry no `name`, so they are never
  submitted, and they reflect the grid — a partly-filled row shows indeterminate.
- **Active modules only.** `BranchPermissionMatrixService::ACTIVE_MODULES` lists
  the modules with a real page (`commission-partners`). `MODULES` still holds the
  Phase 2 names so previously granted permissions stay recognised names, but the
  permissions screen and the branch sidebar both render only the active ones.
  Saving from that screen therefore clears any grant on an inactive module.
- **A Branch Manager's cities are optional.** They can be created before their
  territory is decided, and they pick up a city automatically when assigning a
  Commission Partner to one.

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
withdrawable but not pending, re-settling a month, month isolation, RBAC),
`VipPlanRenewalTest` (activation starts the cycle, expired microsite serves the
503 maintenance page, approve/reject, not-yet-expired refusal, ownership),
`VipPackageRenewalTest` (the four packages' prices/validity/caps, renewal onto a
package, the 15-of-15 block, 15 old + 20 new slots on Professional, downgrade
keeps existing rows, retired package refused), `VipRenewalWindowTest` (the
30-day window, early renewal stacking on remaining days), `MobileNumberTest` (every input shape normalises to 10 digits, duplicates
refused across both forms and across formats, optional, self-ignore on edit),
`AdminFormUsabilityTest` (permission grid lists only live modules and offers
bulk toggles, no Phase 2 rows in the branch sidebar, Branch Manager cities
optional), `CommissionPartnerPasswordTest` (branch-side only, ownership enforced,
non-partner ids 404, sessions cleared), `BranchManagerPasswordTest` (the form is on edit only and nowhere else, reset
clears sessions and rotates the token, non-branch-manager ids 404, RBAC),
`VipMemberCityPickerTest` (the cascade on the add-member form, typed cities,
dedupe by name+state, slug collisions across states, territory granted to both
partner and branch, and no city fields left on the partner form), `LegacyVipPlansRemovedTest` (only four plans remain, the admin screens 404,
commission history keeps its money with a nulled plan), `VipWithdrawalTest`
(the ₹500 floor, the 24-hour cooldown message, wallet debited only on mark-paid,
UTR/screenshot required, View Detail hidden while pending, RBAC).
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
php artisan storage:link   # REQUIRED once per environment - see below
npm run build       # build-bootstrap + vite build
```

### Uploads & the storage link (read before debugging a missing image)

Every upload goes through `->store('uploads', 'public')`, i.e. onto disk at
`storage/app/public/uploads/...`, and the DB stores the returned relative path
(`uploads/xxxx.webp`). All 70-odd views render it as
`asset('storage/'.$path)` -> `/storage/uploads/xxxx.webp`.

That URL only resolves if **`public/storage` is a symlink to
`storage/app/public`**. `public/storage` is gitignored and must therefore be
recreated **once on every environment**:

```bash
php artisan storage:link
```

`storage/app/public/uploads/*` IS committed, so the repo carries the seed
images; the link is what exposes them. Never commit `public/storage` itself —
a real folder there is a point-in-time copy, so anything uploaded afterwards
silently 404s while older images keep working (that exact bug was fixed on
2026-09-21).

#### Production uploads must live OUTSIDE the deploy directory

Production deploys via **Hostinger auto-deploy from git**, which does a shallow
clean checkout (`git log` on the server shows a `grafted` HEAD). Anything the
repo does not contain is destroyed on every deploy. This was confirmed with a
probe file: it was gone after one deploy. Three category images had already been
lost this way, leaving `categories.image` pointing at files that no longer
existed.

Two settings make uploads survive:

1. **`FILESYSTEM_PUBLIC_ROOT`** (`.env`) overrides the `public` disk root and the
   `storage:link` target together. Leave it blank locally (defaults to
   `storage_path('app/public')`); in production set it to an absolute path
   outside the deploy directory, e.g. `/home/<user>/app-uploads`.
2. **The `storage.file` route** (`StorageFileController`) serves
   `/storage/{path}` from the `public` disk whenever the `public/storage` symlink
   is missing — and a clean checkout deletes that symlink too, since it is
   gitignored. Apache serves the symlink directly when it exists, so the route
   only runs as a fallback. It sends long-lived cache headers plus `nosniff` and
   a sandbox CSP, because uploads are user-supplied.

Note the `local` (private) disk has **`'serve' => false`** on purpose: with it
on, the framework registered its own `/storage/{path}` route that shadowed
`storage.file` and sent `Cache-Control: no-store`. That framework route was
signed-URL gated so private files were never exposed, and nothing in the app
reads that disk.

The 19 upload files still force-tracked under `storage/app/public/uploads` are a
leftover from before this fix. Do not untrack them until production is confirmed
to be reading `FILESYSTEM_PUBLIC_ROOT`, because while uploads still live in the
deploy directory that tracking is the only thing restoring them after a deploy.

---

## Suggested additional docs (optional)

These would complement this file if you want them — say the word:
- **`CLAUDE.md`** — a short version of §2/§3/§8 so any AI assistant loads project
  conventions automatically (none exists yet; recommended).
- **`docs/commission.md`** — worked numeric examples of both commission systems.
- **`docs/erd.md`** or a diagram — the model relationships in §4/§5.
- **`.env.example` review** — confirm all required keys (SUPER_ADMIN_*, mail) are
  documented for onboarding.
