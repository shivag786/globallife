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
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>
    <title>{{ $title ?? $adminSiteName }}</title>
    @if (! empty($adminSettings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$adminSettings['favicon']) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden md:hidden" data-sidebar-overlay></div>

        <aside id="admin-sidebar"
               class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 fixed inset-y-0 left-0 z-40 -translate-x-full transition-transform duration-200 md:static md:translate-x-0 overflow-y-auto">
            <div class="px-6 py-5 text-lg font-bold text-white border-b border-slate-800 flex items-center gap-2 justify-between">
                <span class="flex items-center gap-2 min-w-0">
                    @if (! empty($adminSettings['site_logo']))
                        <img src="{{ asset('storage/'.$adminSettings['site_logo']) }}" alt="{{ $adminSiteName }}" class="h-7 w-auto">
                    @endif
                    <span class="truncate">{{ $adminSiteName }}</span>
                </span>
                <button type="button" class="md:hidden text-slate-400 hover:text-white" data-sidebar-close aria-label="Close menu">
                    <x-icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                @if ($user->hasAnyRole(['super_admin', 'admin', 'sub_admin']))
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>

                    @if ($user->hasRole('super_admin'))
                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Website</p>
                        <a href="{{ route('admin.home-sections.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Homepage Builder</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Catalog</p>
                        <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Categories</a>
                        <a href="{{ route('admin.brands.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Brands</a>
                        <a href="{{ route('admin.commissions.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Product Commissions</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Branch &amp; Commission Module</p>
                        <a href="{{ route('admin.cities.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Cities</a>
                        <a href="{{ route('admin.branch-managers.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Branch Managers</a>
                        <a href="{{ route('admin.commission-partners.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Commission Partners</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">VIP Membership</p>
                        <a href="{{ route('admin.vip-plans.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">VIP Plans</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Platform</p>
                        <a href="{{ route('admin.revenue.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue</a>
                        <a href="{{ route('admin.settings.edit') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Site Settings</a>
                        <a href="{{ route('admin.activity-logs.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Activity Log</a>
                    @endif

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Content</p>
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'blog'))
                        <a href="{{ route('admin.blog.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Blog</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'products'))
                        <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Products</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'testimonials'))
                        <a href="{{ route('admin.testimonials.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Testimonials</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'events'))
                        <a href="{{ route('admin.events.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Events</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'media'))
                        <a href="{{ route('admin.media.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Media</a>
                    @endif
                    @if (\App\Services\PermissionMatrixService::userCanAccessModule($user, 'leads'))
                        <a href="{{ route('admin.leads.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>
                    @endif
                    @if ($user->hasAnyRole(['super_admin', 'admin']))
                        <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Orders</a>
                    @endif
                @endif

                @if ($user->hasRole('branch_manager'))
                    <a href="{{ route('branch.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <a href="{{ route('branch.revenue.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue Tracking</a>
                    <a href="{{ route('wallet.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Product Wallet</a>
                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Branch Tools</p>
                    @foreach (\App\Services\BranchPermissionMatrixService::MODULES as $module)
                        @if (\App\Services\BranchPermissionMatrixService::userCanAccessModule($user, $module))
                            @if ($module === 'commission-partners')
                                <a href="{{ route('branch.commission-partners.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Commission Partners</a>
                            @else
                                <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                                    {{ ucwords(str_replace('-', ' ', $module)) }}
                                    <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                                </span>
                            @endif
                        @endif
                    @endforeach
                @endif

                @if ($user->hasRole('commission_partner'))
                    <a href="{{ route('manager.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <a href="{{ route('manager.leads.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>
                    <a href="{{ route('manager.vip-members.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">VIP Members</a>
                    <a href="{{ route('manager.revenue.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue Tracking</a>
                    <a href="{{ route('wallet.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Product Wallet</a>
                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Partner Tools (Phase 2)</p>
                    @foreach (['Sales Tracking', 'Discount Management', 'Customer Management'] as $item)
                        <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                            {{ $item }}
                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                        </span>
                    @endforeach
                @endif

                @if ($user->hasRole('vip_member'))
                    <a href="{{ route('vip.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <a href="{{ route('vip.profile.edit') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Profile</a>
                    <a href="{{ route('vip.modules.edit') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Section Visibility</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Sell Products</p>
                    <a href="{{ route('vip.marketplace.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">My Store</a>
                    <a href="{{ route('wallet.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Product Wallet</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Business Page</p>
                    <a href="{{ route('vip.banners.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Homepage Banner</a>
                    <a href="{{ route('vip.services.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Services</a>
                    <a href="{{ route('vip.products.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Products</a>
                    <a href="{{ route('vip.gallery.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Gallery</a>
                    <a href="{{ route('vip.videos.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">YouTube Videos</a>
                    <a href="{{ route('vip.faqs.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">FAQs</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Customers</p>
                    <a href="{{ route('vip.reviews.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Reviews</a>
                    <a href="{{ route('vip.leads.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">VIP Tools (Phase 2)</p>
                    @foreach (['Offers & Coupons', 'Team Members', 'Bookings'] as $item)
                        <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                            {{ $item }}
                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                        </span>
                    @endforeach
                @endif
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="md:hidden text-slate-500 hover:text-slate-800 flex-shrink-0" data-sidebar-open aria-label="Open menu">
                        <x-icon name="bars" class="w-6 h-6" />
                    </button>
                    <h1 class="text-lg font-semibold truncate">{{ $heading ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 text-sm flex-shrink-0">
                    <span class="hidden sm:inline text-slate-500">{{ $user->name }} &middot; {{ $user->getRoleNames()->implode(', ') }}</span>
                    <button type="button" data-theme-toggle aria-label="Toggle dark mode"
                            class="text-slate-500 hover:text-slate-800 transition">
                        <x-icon name="moon" class="w-5 h-5 dark:hidden" />
                        <x-icon name="sun" class="w-5 h-5 hidden dark:block" />
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Logout</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
                @if (session('status') || session('error'))
                    {{-- Read by resources/js/notify/init.js and shown as a premium toast. --}}
                    <script id="flash-data" type="application/json">@json(['status' => session('status'), 'error' => session('error')])</script>
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
