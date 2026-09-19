<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BranchManagerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CommissionPartnerController as AdminCommissionPartnerController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\PartnerPayoutController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProductBenefitController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\VipMemberController;
use App\Http\Controllers\Admin\VipPlanController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Branch\CommissionPartnerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Manager\LeadController;
use App\Http\Controllers\MicrositeClickController;
use App\Http\Controllers\MicrositeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RazorpayCheckoutController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\Vip\BannerController;
use App\Http\Controllers\Vip\FaqController;
use App\Http\Controllers\Vip\GalleryController;
use App\Http\Controllers\Vip\MarketplaceController;
use App\Http\Controllers\Vip\ModuleVisibilityController;
use App\Http\Controllers\Vip\ProfileController;
use App\Http\Controllers\Vip\ReviewController;
use App\Http\Controllers\Vip\ServiceController;
use App\Http\Controllers\Vip\VideoController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/scooter', function () {
    return view('scooter');
});
Route::get('/vip-plans', [PublicController::class, 'vipPlans'])->name('vip-plans.index');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/category/{category:slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Shopping cart, wishlist & checkout (session-based; open to guests).
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add')->middleware('throttle:60,1');
Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add')->middleware('throttle:60,1');
Route::delete('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
// Identify at checkout: register (set your own password) or log in, then continue.
Route::post('/checkout/register', [CheckoutController::class, 'register'])->name('checkout.register')->middleware('throttle:20,1');
Route::post('/checkout/login', [CheckoutController::class, 'login'])->name('checkout.login')->middleware('throttle:20,1');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:20,1');
// Razorpay: create the gateway order, then verify the signed handler payload
// before the cart is turned into a real order.
Route::post('/checkout/razorpay/create', [RazorpayCheckoutController::class, 'create'])->name('checkout.razorpay.create')->middleware('throttle:20,1');
Route::post('/checkout/razorpay/verify', [RazorpayCheckoutController::class, 'verify'])->name('checkout.razorpay.verify')->middleware('throttle:20,1');
// Razorpay server-to-server webhook. No session, no CSRF (exempted in
// bootstrap/app.php); authenticated by its own HMAC signature instead.
Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');
Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{post}/like', [BlogController::class, 'like'])->name('blog.like')->middleware('throttle:20,1');
Route::post('/blog/{post}/comments', [BlogController::class, 'storeComment'])->name('blog.comments.store')->middleware('throttle:10,1');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/enquiry', [EnquiryController::class, 'store'])->name('enquiry.store')->middleware('throttle:10,1');

Route::middleware(['auth', 'active_account'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product-commission wallet — shared by VIP members, Commission Partners, Branch Managers.
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

    // Customer account area.
    Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [OrderController::class, 'show'])->name('account.orders.show');

    // Saved delivery addresses (address book).
    Route::get('/account/addresses', [AddressController::class, 'index'])->name('account.addresses.index');
    Route::post('/account/addresses', [AddressController::class, 'store'])->name('account.addresses.store')->middleware('throttle:30,1');
    Route::patch('/account/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('account.addresses.default');
    Route::delete('/account/addresses/{address}', [AddressController::class, 'destroy'])->name('account.addresses.destroy');

    Route::middleware('role:super_admin|admin|sub_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('commissions/global', [CommissionController::class, 'editGlobal'])->name('commissions.global.edit');
        Route::put('commissions/global', [CommissionController::class, 'updateGlobal'])->name('commissions.global.update');
        Route::get('commissions/category/{category}', [CommissionController::class, 'editCategory'])->name('commissions.category.edit');
        Route::put('commissions/category/{category}', [CommissionController::class, 'updateCategory'])->name('commissions.category.update');
        Route::get('commissions/product/{product}', [CommissionController::class, 'editProduct'])->name('commissions.product.edit');
        Route::put('commissions/product/{product}', [CommissionController::class, 'updateProduct'])->name('commissions.product.update');

        Route::post('uploads/editor-image', [EditorUploadController::class, 'store'])->name('uploads.editor-image');

        // Permission-gated: Admin has every module permission; Sub Admins only see modules they're granted.
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('products/{product}/benefits', [ProductBenefitController::class, 'index'])->name('products.benefits.index');
        Route::post('products/{product}/benefits', [ProductBenefitController::class, 'store'])->name('products.benefits.store');
        Route::put('products/{product}/benefits/{benefit}', [ProductBenefitController::class, 'update'])->name('products.benefits.update');
        Route::delete('products/{product}/benefits/{benefit}', [ProductBenefitController::class, 'destroy'])->name('products.benefits.destroy');
        Route::resource('blog', BlogPostController::class)
            ->except(['show'])
            ->parameters(['blog' => 'blogPost']);
        Route::resource('testimonials', TestimonialController::class)->except(['show']);

        Route::resource('events', AdminEventController::class)->except(['show']);

        Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::post('media', [AdminMediaController::class, 'store'])->name('media.store');
        Route::delete('media/{mediaItem}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
        Route::patch('media/{mediaItem}/toggle-status', [AdminMediaController::class, 'toggleStatus'])->name('media.toggle-status');

        Route::resource('leads', AdminLeadController::class)->only(['index', 'show', 'update', 'destroy']);

        // Order management — view orders, update status (delivering credits commission).
        Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        // Lightweight JSON poll for the new-order sound alert (registered before the
        // {order} route so "poll" isn't treated as an order_number).
        Route::get('orders/poll', [App\Http\Controllers\Admin\OrderController::class, 'poll'])->name('orders.poll');
        Route::get('orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('orders/{order}/delivery', [App\Http\Controllers\Admin\OrderController::class, 'updateDelivery'])->name('orders.update-delivery');

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('cities', CityController::class)->except(['show']);

            Route::resource('branch-managers', BranchManagerController::class)
                ->except(['show', 'destroy'])
                ->parameters(['branch-managers' => 'branchManager']);
            Route::patch('branch-managers/{branchManager}/toggle-status', [BranchManagerController::class, 'toggleStatus'])
                ->name('branch-managers.toggle-status');
            Route::get('branch-managers/{branchManager}/permissions', [BranchManagerController::class, 'permissions'])
                ->name('branch-managers.permissions.edit');
            Route::put('branch-managers/{branchManager}/permissions', [BranchManagerController::class, 'updatePermissions'])
                ->name('branch-managers.permissions.update');

            Route::get('commission-partners', [AdminCommissionPartnerController::class, 'index'])
                ->name('commission-partners.index');

            // Monthly settlement: what each Commission Partner earned that month
            // (product sales + VIP plans) and marking the month paid.
            Route::get('partner-payouts', [PartnerPayoutController::class, 'index'])
                ->name('partner-payouts.index');
            Route::get('partner-payouts/{partner}', [PartnerPayoutController::class, 'show'])
                ->name('partner-payouts.show');
            Route::post('partner-payouts/{partner}/mark-paid', [PartnerPayoutController::class, 'markPaid'])
                ->name('partner-payouts.mark-paid');

            Route::get('vip-members', [VipMemberController::class, 'index'])
                ->name('vip-members.index');

            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

            Route::get('revenue', [RevenueController::class, 'index'])->name('revenue.index');

            Route::resource('vip-plans', VipPlanController::class)->except(['show']);

            Route::resource('home-sections', HomeSectionController::class)
                ->except(['show'])
                ->parameters(['home-sections' => 'homeSection']);
            Route::patch('home-sections/{homeSection}/toggle-status', [HomeSectionController::class, 'toggleStatus'])
                ->name('home-sections.toggle-status');
            Route::patch('home-sections/{homeSection}/move-up', [HomeSectionController::class, 'moveUp'])
                ->name('home-sections.move-up');
            Route::patch('home-sections/{homeSection}/move-down', [HomeSectionController::class, 'moveDown'])
                ->name('home-sections.move-down');

            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

            // Payment gateway (Razorpay keys + which methods checkout offers).
            Route::get('settings/payment', [PaymentSettingsController::class, 'edit'])->name('settings.payment.edit');
            Route::put('settings/payment', [PaymentSettingsController::class, 'update'])->name('settings.payment.update');
            Route::post('settings/payment/test', [PaymentSettingsController::class, 'test'])->name('settings.payment.test');
        });
    });

    Route::middleware('role:commission_partner')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::resource('vip-members', App\Http\Controllers\Manager\VipMemberController::class)
            ->except(['show', 'destroy'])
            ->parameters(['vip-members' => 'vipMember']);
        Route::patch('vip-members/{vipMember}/toggle-status', [App\Http\Controllers\Manager\VipMemberController::class, 'toggleStatus'])
            ->name('vip-members.toggle-status');
        Route::patch('vip-members/{vipMember}/activate', [App\Http\Controllers\Manager\VipMemberController::class, 'activate'])
            ->name('vip-members.activate');

        Route::get('/revenue', [App\Http\Controllers\Manager\RevenueController::class, 'index'])->name('revenue.index');
    });

    Route::middleware('role:branch_manager')->prefix('branch')->name('branch.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Branch\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('commission-partners', CommissionPartnerController::class)
            ->except(['show', 'destroy'])
            ->parameters(['commission-partners' => 'commissionPartner']);
        Route::patch('commission-partners/{commissionPartner}/toggle-status', [CommissionPartnerController::class, 'toggleStatus'])
            ->name('commission-partners.toggle-status');

        Route::get('/revenue', [App\Http\Controllers\Branch\RevenueController::class, 'index'])->name('revenue.index');
    });

    Route::middleware('role:vip_member')->prefix('vip')->name('vip.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Vip\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/modules', [ModuleVisibilityController::class, 'edit'])->name('modules.edit');
        Route::put('/modules', [ModuleVisibilityController::class, 'update'])->name('modules.update');

        Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('products', App\Http\Controllers\Vip\ProductController::class)->except(['show']);
        Route::resource('gallery', GalleryController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['gallery' => 'galleryItem']);
        Route::resource('videos', VideoController::class)->only(['index', 'store', 'destroy']);
        Route::resource('faqs', FaqController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

        Route::get('/leads', [App\Http\Controllers\Vip\LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [App\Http\Controllers\Vip\LeadController::class, 'export'])->name('leads.export');

        Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::put('marketplace', [MarketplaceController::class, 'update'])->name('marketplace.update');
        Route::resource('gallery', GalleryController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['gallery' => 'galleryItem']);
        Route::resource('videos', VideoController::class)->only(['index', 'store', 'destroy']);
    });
});

// Click-through redirects that log a business_profile_events row before sending
// the visitor on to the real target (tel:, wa.me, maps, external website, booking).
Route::get('/microsite/{vipMicrosite}/click/{type}', [MicrositeClickController::class, 'redirect'])->name('microsite.click');

// Public VIP Member microsite pages, e.g. /jhansi/lifeline-hospital/22-LSTWEFF-44.
// Registered last among top-level routes; the 3-segment shape plus strict `where()`
// constraints keep it from ever shadowing (or being shadowed by) other public routes.
Route::get('/{citySlug}/{businessSlug}/{secureId}', [MicrositeController::class, 'show'])
    ->where(['citySlug' => '[a-z0-9-]+', 'businessSlug' => '[a-z0-9-]+', 'secureId' => '\d+-[A-Za-z0-9]+-\d+'])
    ->name('microsite.show');

Route::post('/{citySlug}/{businessSlug}/{secureId}/reviews', [MicrositeController::class, 'storeReview'])
    ->where(['citySlug' => '[a-z0-9-]+', 'businessSlug' => '[a-z0-9-]+', 'secureId' => '\d+-[A-Za-z0-9]+-\d+'])
    ->middleware('throttle:5,1')
    ->name('microsite.reviews.store');
