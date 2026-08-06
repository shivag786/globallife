# CLAUDE.md

Guidance for AI assistants working in this repo. For the full domain overview see
[project-details.md](project-details.md) — **keep that file updated** after any
structural change (roles, routes, models, commission logic, major flows).

## What this is

**Global Life** — a Laravel 13 / PHP 8.3 platform: a CMS marketing site + an
MLM-style hierarchy (Branch Manager → Commission Partner → VIP Member) + public
VIP microsites + an e-commerce storefront with upline commission splits.

## Commands

```bash
composer dev        # serve + queue + pail logs + vite (concurrently)
composer test       # config:clear + artisan test  (PHPUnit, tests/Feature)
php artisan test --filter=SomeTest
php artisan migrate --seed
npm run build       # runs build-bootstrap.mjs THEN vite build
```

Windows + XAMPP dev environment; shell is PowerShell (Bash tool also available).

## Conventions (match existing code)

- **Model attributes use PHP 8 attributes, not properties**: `#[Fillable([...])]`
  and `#[Hidden([...])]` above the class — not `$fillable` / `$hidden`. Follow this.
- **Business logic lives in Services** (`app/Services`); **read queries in
  Repositories** (`app/Repositories`). Controllers stay thin. Don't put domain
  logic in controllers or models beyond simple accessors.
- **RBAC via spatie/laravel-permission**, `web` guard. Super Admin passes every
  gate through `Gate::before` in `AppServiceProvider` — never add explicit
  super_admin checks; rely on the bypass.
- Permissions for Sub Admin / Branch Manager are **direct-to-user**, driven by
  `PermissionMatrixService` / `BranchPermissionMatrixService` (`{module}.{action}`).
- Route-model binding: `Order` binds by `order_number`, not id.
- Use `activitylog` (`LogsActivity`) for auditing where models already do.

## Critical gotchas — do not trip on these

1. **`/manager` prefix = Commission Partner role** (historical rename from
   `city_manager`). `/branch` = Branch Manager, `/vip` = VIP Member, `/admin` =
   super_admin|admin|sub_admin.
2. **Two SEPARATE commission systems — never conflate them:**
   - *VIP-activation (joining) commission* → `CommissionTransaction` +
     `VipActivationService`, one per microsite.
   - *Product-sale commission* → `ProductCommissionService` + `CommissionRule` +
     `CommissionEarning` + `Wallet`. DB-driven, rule precedence
     product→category→global, company (super_admin) takes the remainder. Earnings
     are **pending** at order time and only credited to wallets on
     `OrderService::markDelivered()` (guarded by `commission_credited`).
3. **Cart never trusts session prices** — `CartService` stores only
   product_id + seller_id + qty; all prices are re-read from the DB at read time.
   Keep it that way.
4. **The public microsite route is registered LAST** in `routes/web.php` with
   tight regex `where()` constraints so it never shadows other routes. Any new
   top-level public route must go *above* it.
5. `EnsureAccountIsActive` (`active_account` middleware) logs out any user whose
   `status !== 'active'`.

## Before finishing a change

- Run `composer test` (or the relevant `--filter`) — there is good Feature
  coverage for commission, RBAC boundaries, cart, and dashboards.
- Update [project-details.md](project-details.md) if the change is structural.
