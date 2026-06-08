<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::crud('product-category', 'ProductCategoryCrudController');
    Route::crud('product-attribute', 'ProductAttributeCrudController');
    Route::crud('product-attribute-value', 'ProductAttributeValueCrudController');
    Route::crud('product', 'ProductCrudController');

    Route::crud('post-category', 'PostCategoryCrudController');
    Route::crud('banner-category', 'BannerCategoryCrudController');
    Route::crud('post', 'PostCrudController');

    Route::get('homepage-layout', 'HomepageLayoutController@index')->name('admin.homepage-layout.index');
    Route::post('homepage-layout', 'HomepageLayoutController@updateOrder')->name('admin.homepage-layout.update');

    Route::crud('order', 'OrderCrudController');
    Route::crud('user', 'UserCrudController');
    Route::crud('contact', 'ContactCrudController');
    Route::crud('api-request-log', 'ApiRequestLogCrudController');
    Route::crud('api-outbound-log', 'ApiOutboundLogCrudController');

    // Dashboard routes
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('dashboard/chart-data', 'DashboardController@getChartData')->name('dashboard.chart-data');

    // Product API routes with rate limiting
    Route::prefix('api')->middleware('throttle:60,1')->group(function () {
        Route::get('attribute-values/{attributeId}', 'ProductCrudController@getAttributeValues')
            ->name('api.attribute.values');
        Route::post('product-attributes', 'ProductCrudController@addProductAttribute')
            ->name('api.product.attributes.add');
        Route::post('product-attributes/remove', 'ProductCrudController@removeProductAttribute')
            ->name('api.product.attributes.remove');
    });

    // ========================================
    // FILE MANAGEMENT ROUTES
    // ========================================
    Route::prefix('files')->name('files.')->middleware('throttle:30,1')->group(function () {
        Route::post('upload-multiple-images', 'FileController@uploadMultipleImages')
            ->name('upload.multiple');
        Route::delete('delete-image', 'FileController@deleteImage')
            ->name('delete');
        
        // PDF Document Upload Routes (Admin only)
        Route::post('upload', 'FileUploadController@upload')
            ->name('upload');
        Route::get('{id}/download', 'FileUploadController@download')
            ->name('admin.download');
        Route::get('{id}/view', 'FileUploadController@view')
            ->name('admin.view');
    });
    Route::crud('product-vehicle-fitment', 'ProductVehicleFitmentCrudController');
}); // this should be the absolute last line of this file



/**
 * DO NOT ADD ANYTHING HERE.
 */
