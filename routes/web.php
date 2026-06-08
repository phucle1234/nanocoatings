<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\WarrantyController as CustomerWarrantyController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Dealer\DashboardController as DealerDashboardController;
use App\Http\Controllers\Dealer\OrderController as DealerOrderController;
use App\Http\Controllers\Dealer\OrderEcommerceController as DealerOrderEcommerceController;
use App\Http\Controllers\Dealer\CartController as DealerCartController;
use App\Http\Controllers\Dealer\WarrantyController as DealerWarrantyController;
use App\Http\Controllers\Dealer\SaleCartController as DealerSaleCartController;
use App\Http\Controllers\Dealer\SaleOrderController as DealerSaleOrderController;
use App\Http\Controllers\Dealer\CustomerController as DealerCustomerController;
use App\Http\Controllers\Dealer\LoanOrderController as DealerLoanOrderController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\LanguageController;

use App\Http\Controllers\langding\HomeController;
use App\Http\Controllers\langding\VoiceSearchController;
use App\Http\Controllers\langding\CategoryController;
use App\Http\Controllers\langding\ProductController;
use App\Http\Controllers\langding\CategorySearchController;
use App\Http\Controllers\langding\PostController;
use App\Http\Controllers\langding\CartController;
use App\Http\Controllers\langding\DemoController;
use App\Http\Controllers\langding\AboutController;
use App\Http\Controllers\langding\TabFeedController;
use App\Http\Controllers\langding\TraceabilityController;
use App\Http\Controllers\langding\WarrantyController;
use App\Http\Controllers\langding\ContactController;
use App\Http\Controllers\langding\BranchController;
use App\Http\Controllers\langding\DocumentController;
use App\Http\Controllers\langding\ShopController;
use App\Http\Controllers\langding\SearchController;
// Language Switcher Route (không cần middleware, xử lý trước)
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Debug Locale Route (chỉ dùng khi development)
Route::get('/debug/locale', function () {
    if (!config('app.debug')) {
        abort(403, 'Debug mode is disabled');
    }

    return response()->json([
        'current_locale' => app()->getLocale(),
        'session_locale' => session('locale'),
        'config_default' => config('languages.default'),
        'config_supported' => config('languages.supported'),
        'route_locale' => request()->route('locale'),
        'app_locale_config' => config('app.locale'),
        'app_fallback_locale' => config('app.fallback_locale'),
        'request_path' => request()->path(),
        'request_url' => request()->url(),
        'all_session' => session()->all(),
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->name('debug.locale');

// Define routes function để tránh duplicate code
$defineRoutes = function () {
    Route::get('/search-bar', [SearchController::class, 'index'])->name('search-bar');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    // ✅ Route cho category news (phải đặt trước route category thường để match trước)
    Route::get('/category/{slug}/news', [CategoryController::class, 'news'])->name('category.news');
    Route::get('/category/{slug?}', [CategoryController::class, 'index'])->name('category');
    Route::get('/product/{slug}/document', [ProductController::class, 'document'])->name('product.document');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.detail');
    Route::get('/category-search', [CategorySearchController::class, 'index'])->name('categorysearch');

    Route::get('/cart/distributors/by-city', [CartController::class, 'distributorsByCity'])
        ->name('cart.distributors.by-city');

    // Mới
    Route::get('/document/{path?}', [DocumentController::class, 'index'])
        ->where('path', '.*')
        ->name('document'); // ← đổi từ 'documents' thành 'document'

    Route::get('/branch/provinces/{countryId}', [BranchController::class, 'getProvinces'])
        ->name('branch.provinces');

    Route::get('/branch/categories/{provinceCode}', [BranchController::class, 'getCategories'])
        ->name('branch.categories');


    Route::get('/branch/{id?}', [BranchController::class, 'branch'])->name('branch');

    Route::get('/distribution-system/{id?}', [BranchController::class, 'distributionSystem'])
        ->name('distribution-system');

    Route::post('/distribution-system/search-distributors', [BranchController::class, 'searchDistributors'])
        ->name('api.distribution.search-distributors');

    Route::post('/distribution-system/search-nearest-distributors', [BranchController::class, 'searchNearestDistributors'])
        ->name('api.distribution.search-nearest-distributors');

    Route::get('/distribution-system/distributors/{code}/showrooms', [BranchController::class, 'getDistributorShowrooms'])
        ->name('api.distribution.distributor-showrooms');

    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    Route::post('/contact/subscribe', [ContactController::class, 'subscribe'])->name('contact.subscribe');
    Route::get('/contact/refresh-captcha', [ContactController::class, 'refreshCaptcha'])->name('contact.refresh-captcha');

    Route::get('/post/{slug}', [PostController::class, 'show'])->name('post.detail');
    Route::get('/post-category/{slug?}', [PostController::class, 'showcategory'])->name('post.category');

    Route::get('/ajax/tab-feed', [TabFeedController::class, 'show'])->name('langding.tab-feed');
    Route::get('/about', [AboutController::class, 'index'])->name('about');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->middleware('guest')->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
    Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/register/{token?}', [RegisterController::class, 'showRegistrationForm'])->middleware('guest')->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');
    Route::get('/preview-email', [RegisterController::class, 'previewEmail']);

    Route::get('/email/verify/{token}', [EmailVerificationController::class, 'verify'])->name('email_verify');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->middleware('guest')->name('forgot-password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->middleware('guest');

    // Khách hàng (customer)
    Route::prefix('customer')->middleware(['auth', 'customer'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('customer.dashboard');
        // Lịch sử đơn hàng
        Route::get('/order', [OrderController::class, 'orderList'])->name('customer.order-list');
        Route::get('/order/new', [OrderController::class, 'orderListNew'])->name('customer.order-list-new');
        Route::get('/order/confirm', [OrderController::class, 'orderListConfirm'])->name('customer.order-list-confirm');
        Route::get('/order/cancel', [OrderController::class, 'orderListCancel'])->name('customer.order-list-cancel');
        Route::get('/order-detail/{id}', [OrderController::class, 'orderDetail'])->name('customer.order-detail');
        Route::get('/warranty', [CustomerWarrantyController::class, 'warrantyList'])->name('customer.warranty-list');
        Route::get('/warranty-detail/{id}', [CustomerWarrantyController::class, 'warrantyDetail'])->name('customer.warranty-detail');
        Route::get('/profile', [CustomerProfileController::class, 'infomation'])->name('customer.profile');
        Route::get('/password', [CustomerProfileController::class, 'password'])->name('customer.password');
        Route::post('/profile/update', [CustomerProfileController::class, 'updateProfile'])->name('customer.profile.update');
        Route::post('/password/update', [CustomerProfileController::class, 'updatePassword'])->name('customer.password.update');

        // Cart routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('cart');
            Route::get('/{orderNumber}', [CartController::class, 'index'])->name('cart.success');
            Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        });
    });

    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::put('/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');
    });

    // Đại lý (dealer)
    Route::prefix('dealer')->middleware(['auth', 'dealer'])->group(function () {
        Route::get('/dashboard', [DealerDashboardController::class, 'index'])->name('dealer.dashboard');
        // Cart
        Route::get('/cart', [DealerCartController::class, 'cart'])->name('dealer.cart');
        Route::get('/cart-checkout', [DealerCartController::class, 'checkout'])->name('dealer.cart-checkout');
        Route::get('/cart-confirm/{id}', [DealerCartController::class, 'confirm'])->name('dealer.cart-confirm');
        Route::get('/cart/product/{id}', [DealerCartController::class, 'productDetail'])->name('dealer.cart.product-detail');
        Route::post('/cart/load-cart', [DealerCartController::class, 'loadCart'])->name('dealer.cart.load-cart');
        Route::post('/cart/add-to-cart', [DealerCartController::class, 'addToCart'])->name('dealer.cart.add-to-cart');
        Route::post('/cart/update-to-cart', [DealerCartController::class, 'updateToCart'])->name('dealer.cart.update-to-cart');
        Route::post('/cart/delete-to-cart', [DealerCartController::class, 'deleteToCart'])->name('dealer.cart.delete-to-cart');
        Route::post('/cart/checkout-info', [DealerCartController::class, 'checkoutInfo'])->name('dealer.cart.checkout-info');
        // Order
        Route::get('/order-history', [DealerOrderController::class, 'orderHistory'])->name('dealer.order-history');
        Route::get('/order-history/new', [DealerOrderController::class, 'orderHistoryNew'])->name('dealer.order-history-new');
        Route::get('/order-history/pending', [DealerOrderController::class, 'orderHistoryPending'])->name('dealer.order-history-pending');
        Route::get('/order-history/responded', [DealerOrderController::class, 'orderHistoryResponded'])->name('dealer.order-history-responded');
        Route::get('/order-history/created', [DealerOrderController::class, 'orderHistoryCreated'])->name('dealer.order-history-created');
        Route::get('/order-history/invoiced', [DealerOrderController::class, 'orderHistoryInvoiced'])->name('dealer.order-history-invoiced');
        Route::get('/order-history/completed', [DealerOrderController::class, 'orderHistoryCompleted'])->name('dealer.order-history-completed');
        Route::get('/order-history/cancelled', [DealerOrderController::class, 'orderHistoryCancelled'])->name('dealer.order-history-cancelled');
        Route::get('/order-history-detail/{id}', [DealerOrderController::class, 'orderHistoryDetail'])->name('dealer.order-history-detail');
        Route::get('/order-history-detail-warehouse/{id}', [DealerOrderController::class, 'orderHistoryDetailWarehouse'])->name('dealer.order-history-detail-warehouse');
        Route::get('/order-diary', [DealerOrderController::class, 'orderDiary'])->name('dealer.order-diary');
        Route::get('/order-diary-detail/{id}', [DealerOrderController::class, 'orderDiaryDetail'])->name('dealer.order-diary-detail');
        // Loan Order
        Route::get('/loan-order', [DealerLoanOrderController::class, 'index'])->name('dealer.loan-order');
        Route::get('/loan-order-partner', [DealerLoanOrderController::class, 'partner'])->name('dealer.loan-order-partner');
        Route::post('/loan-order-partner/submit', [DealerLoanOrderController::class, 'partnerSubmit'])->name('dealer.loan-order-partner.submit');
        Route::get('/loan-order-qr', [DealerLoanOrderController::class, 'qr'])->name('dealer.loan-order-qr');
        Route::post('/loan-order-qr/certification', [DealerLoanOrderController::class, 'qrCertification'])->name('dealer.loan-order-qr.certification');
        Route::get('/loan-order-confirm', [DealerLoanOrderController::class, 'confirm'])->name('dealer.loan-order-confirm');
        Route::post('/loan-order-confirm/submit', [DealerLoanOrderController::class, 'confirmSubmit'])->name('dealer.loan-order-confirm.submit');
        Route::get('/loan-order/product/{id}', [DealerLoanOrderController::class, 'productDetail'])->name('dealer.loan-order.product-detail');
        Route::post('/loan-order/load-cart', [DealerLoanOrderController::class, 'loadCart'])->name('dealer.loan-order.load-cart');
        Route::post('/loan-order/add-to-cart', [DealerLoanOrderController::class, 'addToCart'])->name('dealer.loan-order.add-to-cart');
        Route::post('/loan-order/update-to-cart', [DealerLoanOrderController::class, 'updateToCart'])->name('dealer.loan-order.update-to-cart');
        Route::post('/loan-order/delete-to-cart', [DealerLoanOrderController::class, 'deleteToCart'])->name('dealer.loan-order.delete-to-cart');
        Route::get('/loan-order/dealers-by-city-code', [DealerLoanOrderController::class, 'dealersByCityCode'])->name('dealer.loan-order.dealers-by-city-code');
        // Sale cart
        Route::get('/sale-cart', [DealerSaleCartController::class, 'index'])->name('dealer.sale-cart');
        Route::get('/sale-checkout', [DealerSaleCartController::class, 'checkout'])->name('dealer.sale-checkout');
        Route::post('/sale-checkout-info', [DealerSaleCartController::class, 'checkoutInfo'])->name('dealer.sale-checkout-info');
        Route::get('/sale-confirm/{id}', [DealerSaleCartController::class, 'confirm'])->name('dealer.sale-confirm');
        Route::post('/sale-cart-certification', [DealerSaleCartController::class, 'certification'])->name('dealer.sale-cart-certification');
        Route::get('/sale-cart/product/{id}', [DealerSaleCartController::class, 'productDetail'])->name('dealer.sale-cart.product-detail');
        Route::post('/sale-cart/product-by-qrcode', [DealerSaleCartController::class, 'productByQRCode'])->name('dealer.sale-cart.product-by-qrcode');
        Route::post('/sale-cart/load-cart', [DealerSaleCartController::class, 'loadCart'])->name('dealer.sale-cart.load-cart');
        Route::post('/sale-cart/add-to-cart', [DealerSaleCartController::class, 'addToCart'])->name('dealer.sale-cart.add-to-cart');
        Route::post('/sale-cart/update-to-cart', [DealerSaleCartController::class, 'updateToCart'])->name('dealer.sale-cart.update-to-cart');
        Route::post('/sale-cart/delete-to-cart', [DealerSaleCartController::class, 'deleteToCart'])->name('dealer.sale-cart.delete-to-cart');
        // Sale Order
        Route::get('/sale-order-diary', [DealerSaleOrderController::class, 'orderDiary'])->name('dealer.sale-order-diary');
        Route::get('/sale-order-history', [DealerSaleOrderController::class, 'orderHistory'])->name('dealer.sale-order-history');
        Route::get('/sale-order-history-new', [DealerSaleOrderController::class, 'orderHistoryNew'])->name('dealer.sale-order-history-new');
        Route::get('/sale-order-history-pending', [DealerSaleOrderController::class, 'orderHistoryPending'])->name('dealer.sale-order-history-pending');
        Route::get('/sale-order-history-warehouse', [DealerSaleOrderController::class, 'orderHistoryWarehouse'])->name('dealer.sale-order-history-warehouse');
        Route::get('/sale-order-history-invoice', [DealerSaleOrderController::class, 'orderHistoryInvoice'])->name('dealer.sale-order-history-invoice');
        Route::get('/sale-order-history-delivery', [DealerSaleOrderController::class, 'orderHistoryDelivery'])->name('dealer.sale-order-history-delivery');
        Route::get('/sale-order-history-completed', [DealerSaleOrderController::class, 'orderHistoryCompleted'])->name('dealer.sale-order-history-completed');
        Route::get('/sale-order-history-cancelled', [DealerSaleOrderController::class, 'orderHistoryCancelled'])->name('dealer.sale-order-history-cancelled');
        Route::get('/sale-order-detail/{id}', [DealerSaleOrderController::class, 'orderDetail'])->name('dealer.sale-order-detail');
        // Customer
        Route::get('/customer', [DealerCustomerController::class, 'list'])->name('dealer.customer');
        Route::get('/customer-online', [DealerCustomerController::class, 'listOnline'])->name('dealer.customer-online');
        Route::get('/customer-offline', [DealerCustomerController::class, 'listOffline'])->name('dealer.customer-offline');
        Route::get('/customer-detail/{id}', [DealerCustomerController::class, 'detail'])->name('dealer.customer-detail');
        // Warranty
        Route::get('/warranty', [DealerWarrantyController::class, 'index'])->name('dealer.warranty');
        Route::post('/warranty-search', [DealerWarrantyController::class, 'search'])->name('dealer.warranty-search');
        Route::post('/warranty-certification', [DealerWarrantyController::class, 'certification'])->name('dealer.warranty-certification');
        Route::post('/warranty-request', [DealerWarrantyController::class, 'requestWarranty'])->name('dealer.warranty-request');
        // Order Casumina
        Route::get('/ecommerce', [DealerOrderEcommerceController::class, 'index'])->name('dealer.ecommerce');
        Route::get('/ecommerce-new', [DealerOrderEcommerceController::class, 'new'])->name('dealer.ecommerce-new');
        Route::get('/ecommerce-pending', [DealerOrderEcommerceController::class, 'pending'])->name('dealer.ecommerce-pending');
        Route::get('/ecommerce-warehouse', [DealerOrderEcommerceController::class, 'warehouse'])->name('dealer.ecommerce-warehouse');
        Route::get('/ecommerce-invoice', [DealerOrderEcommerceController::class, 'invoice'])->name('dealer.ecommerce-invoice');
        Route::get('/ecommerce-delivery', [DealerOrderEcommerceController::class, 'delivery'])->name('dealer.ecommerce-delivery');
        Route::get('/ecommerce-completed', [DealerOrderEcommerceController::class, 'completed'])->name('dealer.ecommerce-completed');
        Route::get('/ecommerce-cancelled', [DealerOrderEcommerceController::class, 'cancelled'])->name('dealer.ecommerce-cancelled');
        Route::get('/ecommerce-detail/{id}', [DealerOrderEcommerceController::class, 'detail'])->name('dealer.ecommerce-detail');
        Route::post('/ecommerce-certification', [DealerOrderEcommerceController::class, 'certification'])->name('dealer.ecommerce-certification');
        Route::post('/ecommerce-change-status-order', [DealerOrderEcommerceController::class, 'changeStatusOrder'])->name('dealer.ecommerce-change-status-order');
    });

    Route::get('/demo/branch', [DemoController::class, 'branch'])->name('demo-branch');
    Route::get('/demo/about', [DemoController::class, 'about'])->name('demo-about');
    Route::get('/demo/posts-category', [DemoController::class, 'postsCategory'])->name('demo-posts-category');
    Route::get('/demo/posts', [DemoController::class, 'posts'])->name('demo-posts');
    Route::get('/demo/products-category-venture', [DemoController::class, 'productsCategoryVenture'])->name('demo-products-category-venture');

    Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability');
    Route::get('/traceability/check', [TraceabilityController::class, 'check'])->name('traceability.check');
    Route::get('/traceability/refresh-captcha', [TraceabilityController::class, 'refreshCaptcha'])->name('traceability.refresh-captcha');

    Route::get('/warranty', [WarrantyController::class, 'index'])->name('warranty');
    Route::post('/warranty', [WarrantyController::class, 'store'])->name('warranty.store');
    Route::get('/warranty/refresh-captcha', [WarrantyController::class, 'refreshCaptcha'])->name('warranty.refresh-captcha');
    Route::get('/shop', [ShopController::class, 'shop'])->name('shop');
};

// Routes WITHOUT locale prefix: /... (uses session, fallback for user convenience)
Route::middleware('setlocale')->group($defineRoutes);

// Routes WITH locale prefix: /vi/..., /en/... (SEO friendly URLs)
// Route::prefix('{locale}')->middleware('setlocale')->group($defineRoutes);


// Routes upload hình ảnh cho admin
Route::middleware(['web', 'auth:backpack'])->group(function () {
    Route::post('/admin/upload-image', [ImageUploadController::class, 'upload'])->name('admin.upload-image');
    Route::post('/admin/upload-multiple-images', [ImageUploadController::class, 'uploadMultiple'])->name('admin.upload-multiple-images');
    Route::delete('/admin/delete-image', [ImageUploadController::class, 'delete'])->name('admin.delete-image');
});

// Routes download/view file PDF cho user thường (cần auth)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/files/{id}/download', [\App\Http\Controllers\Admin\FileUploadController::class, 'download'])->name('files.download');
    Route::get('/files/{id}/view', [\App\Http\Controllers\Admin\FileUploadController::class, 'view'])->name('files.view');
});
