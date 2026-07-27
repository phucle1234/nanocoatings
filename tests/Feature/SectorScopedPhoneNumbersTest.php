<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Providers\ViewServiceProvider;
use App\Services\SectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use ReflectionMethod;
use Tests\TestCase;

class SectorScopedPhoneNumbersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_contact_phone_numbers_filters_to_phone_icon_items_and_scopes_by_sector(): void
    {
        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-phones-'.uniqid();
        $sector->handleTranslations(['name_vi' => 'Test Sector Phones', 'slug_vi' => $slug]);
        $sectorService->syncSectorResources($sector);

        $contactSlug = $sectorService->getBannerCategorySlug($sector, 'footer-lien-he', 'vi');
        $contactCategory = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $contactSlug))
            ->first();

        $addressPost = Post::create(['post_type' => 'banner', 'is_active' => true, 'sort_order' => 1]);
        $addressPost->postcategories()->attach($contactCategory->id, ['is_primary' => true, 'sort_order' => 1]);
        $addressPost->handleTranslations([
            'title_vi' => 'Dia chi test',
            'image_urls_vi' => '/storage/images/icon-location.svg',
        ]);

        $phonePost = Post::create(['post_type' => 'banner', 'is_active' => true, 'sort_order' => 2]);
        $phonePost->postcategories()->attach($contactCategory->id, ['is_primary' => true, 'sort_order' => 2]);
        $phonePost->handleTranslations([
            'title_vi' => '0912 345 678',
            'image_urls_vi' => '/storage/images/telephone-call.svg',
        ]);

        $route = new Route('GET', '/applications/{slug}', ['as' => 'sectors.show']);
        $route->bind(Request::create('/applications/'.$slug, 'GET'));
        $request = Request::create('/applications/'.$slug, 'GET');
        $request->setRouteResolver(fn () => $route);
        app()->instance('request', $request);
        app()->setLocale('vi');

        $provider = new ViewServiceProvider(app());
        $method = new ReflectionMethod($provider, 'contactPhoneNumbers');
        $method->setAccessible(true);

        $phones = $method->invoke($provider);

        $this->assertCount(1, $phones);
        $this->assertSame('0912 345 678', $phones->first()->title);
    }

    public function test_header_no_longer_hardcodes_phone_numbers(): void
    {
        $source = file_get_contents(resource_path('views/langding/components/header.blade.php'));

        $this->assertStringNotContainsString('0987949494', $source);
        $this->assertStringNotContainsString('01426529', $source);
        $this->assertStringContainsString('$headerPhones', $source);
    }

    public function test_sidebar_no_longer_hardcodes_phone_numbers(): void
    {
        $source = file_get_contents(resource_path('views/langding/index.blade.php'));

        $this->assertStringNotContainsString('0987949494', $source);
        $this->assertStringNotContainsString('01426529', $source);
        $this->assertStringContainsString('$sidebarPhones', $source);
    }
}
