<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BranchManagerController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CommissionPartnerController as AdminCommissionPartnerController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\VipPlanController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MicrositeClickController;
use App\Http\Controllers\MicrositeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/experience', [PublicController::class, 'experience'])->name('experience');
Route::get('/scooter',function()
{
    return view('scooter');
});
Route::get('/vip-plans', [PublicController::class, 'vipPlans'])->name('vip-plans.index');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Shopping cart, wishlist & checkout (session-based; open to guests).
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add')->middleware('throttle:60,1');
Route::patch('/cart/update', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [\App\Http\Controllers\WishlistController::class, 'add'])->name('wishlist.add')->middleware('throttle:60,1');
Route::delete('/wishlist/remove', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('wishlist.remove');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:20,1');
Route::get('/checkout/confirmation/{order}', [\App\Http\Controllers\CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
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
    Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');

    // Customer account area.
    Route::get('/account/orders', [\App\Http\Controllers\Account\OrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [\App\Http\Controllers\Account\OrderController::class, 'show'])->name('account.orders.show');

    Route::middleware('role:super_admin|admin|sub_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::post('uploads/editor-image', [EditorUploadController::class, 'store'])->name('uploads.editor-image');

        // Permission-gated: Admin has every module permission; Sub Admins only see modules they're granted.
        Route::resource('products', AdminProductController::class)->except(['show']);

        // Per-product benefits management (shown in the customer "Benefits" popup).
        Route::get('products/{product}/benefits', [\App\Http\Controllers\Admin\ProductBenefitController::class, 'index'])->name('products.benefits.index');
        Route::post('products/{product}/benefits', [\App\Http\Controllers\Admin\ProductBenefitController::class, 'store'])->name('products.benefits.store');
        Route::put('products/{product}/benefits/{benefit}', [\App\Http\Controllers\Admin\ProductBenefitController::class, 'update'])->name('products.benefits.update');
        Route::delete('products/{product}/benefits/{benefit}', [\App\Http\Controllers\Admin\ProductBenefitController::class, 'destroy'])->name('products.benefits.destroy');
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
        Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('cities', CityController::class)->except(['show']);

            // Catalog configuration — Super Admin only.
            Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show']);
            Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)->except(['show']);

            // Product-sale commission configuration (separate from VIP joining commission).
            Route::get('commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
            Route::get('commissions/global', [\App\Http\Controllers\Admin\CommissionController::class, 'editGlobal'])->name('commissions.global.edit');
            Route::put('commissions/global', [\App\Http\Controllers\Admin\CommissionController::class, 'updateGlobal'])->name('commissions.global.update');
            Route::get('commissions/category/{category}', [\App\Http\Controllers\Admin\CommissionController::class, 'editCategory'])->name('commissions.category.edit');
            Route::put('commissions/category/{category}', [\App\Http\Controllers\Admin\CommissionController::class, 'updateCategory'])->name('commissions.category.update');
            Route::get('commissions/product/{product}', [\App\Http\Controllers\Admin\CommissionController::class, 'editProduct'])->name('commissions.product.edit');
            Route::put('commissions/product/{product}', [\App\Http\Controllers\Admin\CommissionController::class, 'updateProduct'])->name('commissions.product.update');

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

            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

            Route::get('revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue.index');

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
        });
    });

    Route::middleware('role:commission_partner')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/leads', [\App\Http\Controllers\Manager\LeadController::class, 'index'])->name('leads.index');
        Route::resource('vip-members', \App\Http\Controllers\Manager\VipMemberController::class)
            ->except(['show', 'destroy'])
            ->parameters(['vip-members' => 'vipMember']);
        Route::patch('vip-members/{vipMember}/toggle-status', [\App\Http\Controllers\Manager\VipMemberController::class, 'toggleStatus'])
            ->name('vip-members.toggle-status');
        Route::patch('vip-members/{vipMember}/activate', [\App\Http\Controllers\Manager\VipMemberController::class, 'activate'])
            ->name('vip-members.activate');

        Route::get('/revenue', [\App\Http\Controllers\Manager\RevenueController::class, 'index'])->name('revenue.index');
    });

    Route::middleware('role:branch_manager')->prefix('branch')->name('branch.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Branch\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('commission-partners', \App\Http\Controllers\Branch\CommissionPartnerController::class)
            ->except(['show', 'destroy'])
            ->parameters(['commission-partners' => 'commissionPartner']);
        Route::patch('commission-partners/{commissionPartner}/toggle-status', [\App\Http\Controllers\Branch\CommissionPartnerController::class, 'toggleStatus'])
            ->name('commission-partners.toggle-status');

        Route::get('/revenue', [\App\Http\Controllers\Branch\RevenueController::class, 'index'])->name('revenue.index');
    });

    Route::middleware('role:vip_member')->prefix('vip')->name('vip.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Vip\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\Vip\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Vip\ProfileController::class, 'update'])->name('profile.update');

        Route::get('/modules', [\App\Http\Controllers\Vip\ModuleVisibilityController::class, 'edit'])->name('modules.edit');
        Route::put('/modules', [\App\Http\Controllers\Vip\ModuleVisibilityController::class, 'update'])->name('modules.update');

        Route::resource('banners', \App\Http\Controllers\Vip\BannerController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('services', \App\Http\Controllers\Vip\ServiceController::class)->except(['show']);
        Route::resource('products', \App\Http\Controllers\Vip\ProductController::class)->except(['show']);

        // Sell Super-Admin catalog products on the storefront (visibility/featured/order only).
        Route::get('marketplace', [\App\Http\Controllers\Vip\MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::put('marketplace', [\App\Http\Controllers\Vip\MarketplaceController::class, 'update'])->name('marketplace.update');
        Route::resource('gallery', \App\Http\Controllers\Vip\GalleryController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['gallery' => 'galleryItem']);
        Route::resource('videos', \App\Http\Controllers\Vip\VideoController::class)->only(['index', 'store', 'destroy']);
        Route::resource('faqs', \App\Http\Controllers\Vip\FaqController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/reviews', [\App\Http\Controllers\Vip\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/approve', [\App\Http\Controllers\Vip\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/reviews/{review}/reject', [\App\Http\Controllers\Vip\ReviewController::class, 'reject'])->name('reviews.reject');

        Route::get('/leads', [\App\Http\Controllers\Vip\LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [\App\Http\Controllers\Vip\LeadController::class, 'export'])->name('leads.export');
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
Route::get('/fix-storage-link-xyz123', function () {
    $link = public_path('storage');
    $target = storage_path('app/public');

    // Remove the fake folder (only if it's NOT a symlink)
    if (file_exists($link) && !is_link($link)) {
        // Recursively delete the folder contents first
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($link, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($link);
    }

    // Now try creating a real symlink
    if (symlink($target, $link)) {
        return 'Success! Real symlink created: ' . $link . ' → ' . $target;
    }

    return 'symlink() failed — function may be disabled on this host. Use the copy-based fallback instead.';
});
