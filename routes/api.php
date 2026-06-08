<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\langding\VoiceSearchController;
use App\Http\Controllers\langding\BranchController;
use App\Http\Controllers\langding\VehicleDataController;

use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\NppWebhookController;
use App\Http\Controllers\Api\ProductUpsertController;

/*
|--------------------------------------------------------------------------
| Public API - Frontend / App
|--------------------------------------------------------------------------
*/

// Unified search
Route::post('/search', [VoiceSearchController::class, 'search'])->name('api.search');

// Backward compatibility
Route::post('/voice-search', [VoiceSearchController::class, 'search']);

Route::post('/search-dealers', [BranchController::class, 'searchDealers'])
    ->name('api.search-dealers');

Route::post('/search-nearest-dealers', [BranchController::class, 'searchNearestDealers'])
    ->name('api.search-nearest-dealers');

Route::get('/search-all-dealers', [BranchController::class, 'searchAllDealers'])
    ->name('api.search-all-dealers');

// Vehicle data
Route::get('/vehicle-data/{vehicleType}', [VehicleDataController::class, 'getVehicleData'])
    ->where('vehicleType', 'oto|xe-may|xe-tai')
    ->name('api.vehicle-data');

/*
|--------------------------------------------------------------------------
| Public API - Categories
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryApiController::class, 'index'])->name('api.categories.index');
    Route::get('/featured', [CategoryApiController::class, 'featured'])->name('api.categories.featured');
    Route::get('/root', [CategoryApiController::class, 'root'])->name('api.categories.root');
    Route::get('/{identifier}', [CategoryApiController::class, 'show'])->name('api.categories.show');
    Route::get('/{identifier}/children', [CategoryApiController::class, 'children'])->name('api.categories.children');
    Route::get('/{identifier}/products', [CategoryApiController::class, 'products'])->name('api.categories.products');
});

/*
|--------------------------------------------------------------------------
| Public API - Products
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function () {
    Route::get('/by-category', [ProductApiController::class, 'getProductsByCategory'])
        ->name('api.products.by-category');

    Route::get('/{slug}', [ProductApiController::class, 'getProductDetail'])
        ->name('api.products.detail');
});

/*
|--------------------------------------------------------------------------
| Public API - Post Categories
|--------------------------------------------------------------------------
*/

Route::prefix('post-categories')->group(function () {
    Route::get('/', [PostCategoryApiController::class, 'index'])->name('api.post-categories.index');
    Route::get('/featured', [PostCategoryApiController::class, 'featured'])->name('api.post-categories.featured');
    Route::get('/root', [PostCategoryApiController::class, 'root'])->name('api.post-categories.root');
    Route::get('/{identifier}', [PostCategoryApiController::class, 'show'])->name('api.post-categories.show');
    Route::get('/{identifier}/children', [PostCategoryApiController::class, 'children'])->name('api.post-categories.children');
    Route::get('/{identifier}/posts', [PostCategoryApiController::class, 'posts'])->name('api.post-categories.posts');
});

/*
|--------------------------------------------------------------------------
| Public API - Posts
|--------------------------------------------------------------------------
*/

Route::prefix('posts')->group(function () {
    Route::get('/by-category', [PostApiController::class, 'getPostsByCategory'])
        ->name('api.posts.by-category');
});

/*
|--------------------------------------------------------------------------
| Integration API - External systems
| Giữ nguyên endpoint cũ để không ảnh hưởng đối tác
|--------------------------------------------------------------------------
*/

Route::middleware(['api.request.log'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Product integration
    |--------------------------------------------------------------------------
    */
    Route::post('/products', [ProductUpsertController::class, 'store'])
        ->name('api.products.store');

    Route::post('/products/update-by-sku', [ProductUpsertController::class, 'updateBySku'])
        ->name('api.products.updateBySku');
    Route::post('/products/update-price', [ProductUpsertController::class, 'updatePrice'])
        ->name('api.products.updatePrice');
    /*
    |--------------------------------------------------------------------------
    | NPP / Dealer webhook integration
    |--------------------------------------------------------------------------
    */
    Route::controller(NppWebhookController::class)->prefix('webhook')->group(function () {
        Route::post('/npp-insert', 'insertNppAll');
        Route::post('/npp-create-account', 'createAccount');
        Route::post('/npp-update-account', 'updateAccount');
        Route::post('/npp-insert-category', 'insertCategoryNpp');
        Route::post('/npp-delete-category', 'deleteCategoryNpp');
    });

    /*
    |--------------------------------------------------------------------------
    | User / Customer integration
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->controller(UserApiController::class)->group(function () {
        Route::post('/customer-create-account', 'customerCreateAccount');
        Route::post('/customer-update-account', 'customerUpdateAccount');
        Route::post('/update', 'updateProfile');
        Route::post('/update-status', 'updateStatus');
    });

    /*
    |--------------------------------------------------------------------------
    | Order integration
    |--------------------------------------------------------------------------
    */
    Route::controller(OrderApiController::class)->prefix('orders')->group(function () {
        Route::post('/show', 'show');
        Route::post('/status', 'updateStatus');
        Route::post('/update-npp-for-order', 'updateNPPForOrder');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    //
});

/*
|--------------------------------------------------------------------------
| Internal test routes
|--------------------------------------------------------------------------
| Không nên để lâu trên production
|--------------------------------------------------------------------------
*/



Route::post('/test-upload', function (Request $request) {
    try {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $filename, 'public');

            return response()->json([
                'success' => true,
                'url' => '/storage/images/' . $filename,
                'filename' => $filename,
                'path' => $path,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không có file được upload',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ]);
    }
});
