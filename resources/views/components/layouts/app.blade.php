@php
    $user = auth()->user();
    $adminSettings = app(\App\Services\SettingsService::class)->all();
    $adminSiteName = $adminSettings['site_title'] ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- No-flash theme init: set data-theme before first paint from saved choice or system preference. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t !== 'dark' && t !== 'light') {
                    t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
    <title>{{ $title ?? $adminSiteName }}</title>
    @if (! empty($adminSettings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$adminSettings['favicon']) }}">
    @endif
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="admin-shell">
    <div id="sidebar-overlay" class="admin-sidebar-backdrop d-none" data-sidebar-overlay></div>

    <aside id="admin-sidebar" class="admin-sidebar">
        <div class="d-flex align-items-center justify-content-between gap-2 px-4 py-3 border-bottom border-light border-opacity-10">
            <span class="d-flex align-items-center gap-2 min-w-0 fs-5 fw-bold text-white">
                @if (! empty($adminSettings['site_logo']))
                    <img src="{{ asset('storage/'.$adminSettings['site_logo']) }}" alt="{{ $adminSiteName }}"
                         style="height: 1.75rem; width: auto">
                @endif
                <span class="text-truncate">{{ $adminSiteName }}</span>
            </span>
            <button type="button" class="btn btn-sm btn-link text-white-50 p-0 d-lg-none"
                    data-sidebar-close aria-label="Close menu">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>
        <nav class="px-3 py-3 small">
                @if ($user->hasAnyRole(['super_admin', 'admin', 'sub_admin']))
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>

                    @if ($user->hasRole('super_admin'))
                        <p class="admin-sidebar-heading">Website</p>
                        <a href="{{ route('admin.home-sections.index') }}">Homepage Builder</a>

                        <p class="admin-sidebar-heading">Catalog</p>
                        <a href="{{ route('admin.categories.index') }}">Categories</a>
                        <a href="{{ route('admin.brands.index') }}">Brands</a>
                        <a href="{{ route('admin.commissions.index') }}">Product Commissions</a>

                        <p class="admin-sidebar-heading">Branch &amp; Commission Module</p>
                        <a href="{{ route('admin.cities.index') }}">Cities</a>
                        <a href="{{ route('admin.sub-admins.index') }}">Sub Admins</a>
                        <a href="{{ route('admin.branch-managers.index') }}">Branch Managers</a>
                        <a href="{{ route('admin.commission-partners.index') }}">Commission Partners</a>
                        <a href="{{ route('admin.partner-payouts.index') }}">Partner Payouts</a>
                        <a href="{{ route('admin.withdrawals.index') }}">VIP Withdrawals</a>

                        <p class="admin-sidebar-heading">Platform</p>
                        <a href="{{ route('admin.revenue.index') }}">Revenue</a>
                        <a href="{{ route('admin.settings.edit') }}">Site Settings</a>
                        <a href="{{ route('admin.settings.payment.edit') }}">Payment Gateway</a>
                        <a href="{{ route('admin.activity-logs.index') }}">Activity Log</a>
                    @endif

                    {{-- A Sub Admin granted the branch-managers module runs the
                         Branch Managers they created. Super Admin reaches the same
                         screen from the Branch & Commission Module block above, so
                         this is only rendered for everyone else. --}}
                    @if (! $user->hasRole('super_admin') && \App\Services\PermissionMatrixService::userCanAccessModule($user, 'branch-managers'))
                        <p class="admin-sidebar-heading">My Network</p>
                        <a href="{{ route('admin.branch-managers.index') }}">Branch Managers</a>
                    @endif

                    <p class="admin-sidebar-heading">Content</p>
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'blog'))
                        <a href="{{ route('admin.blog.index') }}">Blog</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'products'))
                        <a href="{{ route('admin.products.index') }}">Products</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'testimonials'))
                        <a href="{{ route('admin.testimonials.index') }}">Testimonials</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'events'))
                        <a href="{{ route('admin.events.index') }}">Events</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'media'))
                        <a href="{{ route('admin.media.index') }}">Media</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'leads'))
                        <a href="{{ route('admin.leads.index') }}">Leads</a>
                    @endif
                    @if ($user->hasAnyRole(['super_admin', 'admin']))
                        <a href="{{ route('admin.orders.index') }}" data-order-nav
                           class="d-flex align-items-center justify-content-between">
                            <span>Orders</span>
                            <span data-order-badge
                                  class="d-none ms-2 badge rounded-pill text-bg-danger font-monospace"></span>
                        </a>
                    @endif
                @endif

                @if ($user->hasRole('branch_manager'))
                    <a href="{{ route('branch.dashboard') }}">Dashboard</a>
                    <a href="{{ route('branch.revenue.index') }}">Revenue Tracking</a>
                    <a href="{{ route('wallet.index') }}">Product Wallet</a>
                    <p class="admin-sidebar-heading">Branch Tools</p>
                    {{-- Only modules with a real page; the Phase 2 names are not
                         listed at all rather than shown as dead "soon" rows. --}}
                    @foreach (\App\Services\BranchPermissionMatrixService::activeModules() as $module)
                        @if (\App\Services\BranchPermissionMatrixService::userCanAccessModule($user, $module))
                            <a href="{{ route('branch.commission-partners.index') }}">Commission Partners</a>
                        @endif
                    @endforeach
                @endif

                @if ($user->hasRole('commission_partner'))
                    <a href="{{ route('manager.dashboard') }}">Dashboard</a>
                    <a href="{{ route('manager.leads.index') }}">Leads</a>
                    <a href="{{ route('manager.vip-members.index') }}">VIP Members</a>
                    <a href="{{ route('manager.revenue.index') }}">Revenue Tracking</a>
                    <a href="{{ route('wallet.index') }}">Product Wallet</a>
                    <!--<p class="admin-sidebar-heading">Partner Tools (Phase 2)</p>-->
                    <!--@foreach (['Sales Tracking', 'Discount Management', 'Customer Management'] as $item)-->
                    <!--    <span class="d-flex align-items-center justify-content-between px-3 py-2 rounded text-secondary">-->
                    <!--        {{ $item }}-->
                    <!--        <span class="badge text-bg-dark">soon</span>-->
                    <!--    </span>-->
                    <!--@endforeach-->
                @endif

                @if ($user->hasRole('vip_member'))
                    <a href="{{ route('vip.dashboard') }}">Dashboard</a>
                    <a href="{{ route('vip.profile.edit') }}">Profile</a>
                    <a href="{{ route('vip.modules.edit') }}">Section Visibility</a>

                    <p class="admin-sidebar-heading">Earnings</p>
                    <a href="{{ route('wallet.index') }}">Wallet &amp; Withdrawals</a>

                    <p class="admin-sidebar-heading">Sell Products</p>
                    <a href="{{ route('vip.marketplace.index') }}">My Store</a>

                    <p class="admin-sidebar-heading">Business Page</p>
                    <a href="{{ route('vip.banners.index') }}">Homepage Banner</a>
                    <a href="{{ route('vip.services.index') }}">Services</a>
                    <a href="{{ route('vip.products.index') }}">Products</a>
                    <a href="{{ route('vip.gallery.index') }}">Gallery</a>
                    <a href="{{ route('vip.videos.index') }}">YouTube Videos</a>
                    <a href="{{ route('vip.faqs.index') }}">FAQs</a>

                    <p class="admin-sidebar-heading">Customers</p>
                    <a href="{{ route('vip.reviews.index') }}">Reviews</a>
                    <a href="{{ route('vip.leads.index') }}">Leads</a>

                    <p class="admin-sidebar-heading">VIP Tools (Phase 2)</p>
                    @foreach (['Offers & Coupons', 'Team Members', 'Bookings'] as $item)
                        <span class="d-flex align-items-center justify-content-between px-3 py-2 rounded text-secondary">
                            {{ $item }}
                            <span class="badge text-bg-dark">soon</span>
                        </span>
                    @endforeach
                @endif
        </nav>
    </aside>

    <div class="admin-main d-flex flex-column min-vh-100">
        <header class="bg-body border-bottom d-flex align-items-center justify-content-between gap-3 px-3 px-sm-4 py-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button type="button" class="btn btn-sm btn-link text-secondary p-0 d-lg-none"
                        data-sidebar-open aria-label="Open menu">
                    <x-icon name="bars" class="w-6 h-6" />
                </button>
                <h1 class="h5 fw-semibold mb-0 text-truncate">{{ $heading ?? 'Dashboard' }}</h1>
            </div>
            <div class="d-flex align-items-center gap-2 gap-sm-3 small flex-shrink-0">
                <span class="d-none d-sm-inline text-secondary">
                    {{ $user->name }} &middot; {{ $user->getRoleNames()->implode(', ') }}
                </span>
                <button type="button" data-theme-toggle aria-label="Toggle dark mode"
                        class="btn btn-sm btn-link text-secondary p-0">
                    <x-icon name="moon" class="w-5 h-5" data-icon-light />
                    <x-icon name="sun" class="w-5 h-5 d-none" data-icon-dark />
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-grow-1 p-3 p-sm-4 overflow-x-hidden">
                @if (session('status') || session('error'))
                    {{-- Read by resources/js/notify/init.js and shown as a premium toast. --}}
                    <script id="flash-data" type="application/json">@json(['status' => session('status'), 'error' => session('error')])</script>
                @endif

            @if ($errors->any())
                <div class="alert alert-danger small">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @if ($user->hasAnyRole(['super_admin', 'admin']))
        @include('partials.admin-order-alert')
    @endif
</body>
</html>
