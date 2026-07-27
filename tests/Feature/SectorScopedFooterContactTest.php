<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Providers\ViewServiceProvider;
use App\Services\SectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use ReflectionMethod;
use Tests\TestCase;

class SectorScopedFooterContactTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sector_layout_registers_footer_contact_about_social_banner_keys(): void
    {
        $bannerKeys = config('sector_layout.banner_keys');

        $this->assertSame('footer-lien-he', $bannerKeys['footer_contact'] ?? null);
        $this->assertSame('footer-ve-nanocoatings', $bannerKeys['footer_about'] ?? null);
        $this->assertSame('ket-noi-voi-casumina', $bannerKeys['footer_social'] ?? null);
    }

    public function test_sync_sector_resources_provisions_per_sector_contact_and_social_banner_categories(): void
    {
        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-contact-'.uniqid();
        $sector->handleTranslations([
            'name_vi' => 'Test Sector Contact',
            'slug_vi' => $slug,
        ]);

        $sectorService->syncSectorResources($sector);

        foreach (['footer-lien-he', 'footer-ve-nanocoatings', 'ket-noi-voi-casumina'] as $literalSlug) {
            $expectedSlug = $sectorService->getBannerCategorySlug($sector, $literalSlug);
            $exists = PostCategory::withoutGlobalScopes()
                ->whereHas('translations', fn ($q) => $q->where('slug', $expectedSlug))
                ->exists();

            $this->assertTrue($exists, "Expected banner category with slug [{$expectedSlug}] to have been auto-provisioned.");
        }
    }

    public function test_view_service_provider_resolves_sector_aware_banner_slug(): void
    {
        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-contact-2-'.uniqid();
        $sector->handleTranslations([
            'name_vi' => 'Test Sector Contact 2',
            'slug_vi' => $slug,
        ]);

        $route = new Route('GET', '/applications/{slug}', ['as' => 'sectors.show']);
        $route->bind(Request::create('/applications/'.$slug, 'GET'));
        $request = Request::create('/applications/'.$slug, 'GET');
        $request->setRouteResolver(fn () => $route);
        app()->instance('request', $request);

        $provider = new ViewServiceProvider(app());
        $method = new ReflectionMethod($provider, 'sectorAwareBannerSlug');
        $method->setAccessible(true);

        $expected = $sectorService->getBannerCategorySlug($sector, 'footer-lien-he');
        $this->assertSame($expected, $method->invoke($provider, 'footer-lien-he'));

        // Homepage / non-sector route falls back to the literal slug.
        $homeRoute = new Route('GET', '/', ['as' => 'home']);
        $homeRequest = Request::create('/', 'GET');
        $homeRequest->setRouteResolver(fn () => $homeRoute);
        app()->instance('request', $homeRequest);

        $this->assertSame('footer-lien-he', $method->invoke($provider, 'footer-lien-he'));
    }
}
