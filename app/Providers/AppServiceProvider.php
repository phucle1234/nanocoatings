<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\LangdingComposer;
use App\Services\ExternalProductImporter;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExternalProductImporter::class, fn() => new ExternalProductImporter());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Meilisearch sync được handle trực tiếp trong ProductCrudController
        // sau khi generateAndSaveTextSearch()
        // ========================================
        // MEILISEARCH SYNC STRATEGY
        // ========================================
        // 
        // Chúng ta KHÔNG dùng Observers cho Meilisearch sync vì:
        // 1. Tránh infinite loop khi save translations
        // 2. Better performance (chỉ sync 1 lần, không phải mỗi translation)
        // 3. Explicit control trong Controller (dễ debug, dễ test)
        // 
        // Sync logic được handle trong:
        // - ProductCrudController->store() (Line 477-480)
        // - ProductCrudController->update() (Line 539-542)
        // 
        // Flow:
        // 1. handleTranslations() → Save translations
        // 2. generateAndSaveTextSearch() → Generate text_search
        // 3. searchable() → Sync to Meilisearch
        //
        // Xem thêm: docs/PRODUCT_AUTO_TEXT_SEARCH.md
        // ========================================
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        // Register View Composer cho langding views
        View::composer('langding.*', LangdingComposer::class);
    }
}
