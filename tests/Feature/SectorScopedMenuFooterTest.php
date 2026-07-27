<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Models\ProductCategory;
use App\Providers\ViewServiceProvider;
use App\Services\SectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use ReflectionMethod;
use Tests\TestCase;

class SectorScopedMenuFooterTest extends TestCase
{
    use DatabaseTransactions;

    private function makeSectorAndCategories(): array
    {
        $hub = app(SectorService::class)->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-scope-'.uniqid();
        $sector->handleTranslations([
            'name_vi' => 'Test Sector Scope',
            'slug_vi' => $slug,
        ]);

        $sectorOnlyCategory = ProductCategory::create([
            'code' => 'test-cat-sector-'.uniqid(),
            'is_active' => true,
            'display_scopes' => ['homepage' => false, 'sector_ids' => [$sector->id]],
        ]);
        $sectorOnlyCategory->handleTranslations(['name_vi' => 'Danh muc chi thuoc nganh test']);

        $homepageOnlyCategory = ProductCategory::create([
            'code' => 'test-cat-home-'.uniqid(),
            'is_active' => true,
            'display_scopes' => ['homepage' => true, 'sector_ids' => []],
        ]);
        $homepageOnlyCategory->handleTranslations(['name_vi' => 'Danh muc chi thuoc trang chu']);

        return [$sector, $slug, $sectorOnlyCategory, $homepageOnlyCategory];
    }

    public function test_root_categories_api_scopes_by_sector_id(): void
    {
        app()->setLocale('vi');
        [$sector, , $sectorOnlyCategory, $homepageOnlyCategory] = $this->makeSectorAndCategories();

        $sectorScoped = $this->getJson('/api/categories/root?sector_id='.$sector->id);
        $sectorScoped->assertOk();
        $sectorNames = collect($sectorScoped->json('data'))->pluck('name');
        $this->assertTrue($sectorNames->contains('Danh muc chi thuoc nganh test'));
        $this->assertFalse($sectorNames->contains('Danh muc chi thuoc trang chu'));

        $homepageScoped = $this->getJson('/api/categories/root');
        $homepageScoped->assertOk();
        $homeNames = collect($homepageScoped->json('data'))->pluck('name');
        $this->assertTrue($homeNames->contains('Danh muc chi thuoc trang chu'));
        $this->assertFalse($homeNames->contains('Danh muc chi thuoc nganh test'));
    }

    public function test_get_child_categories_scopes_by_sector_id(): void
    {
        app()->setLocale('vi');
        $hub = app(SectorService::class)->getOrCreateHubCategory();
        $sector = PostCategory::create(['parent_id' => $hub->id, 'is_active' => true, 'is_sector' => true, 'sort_order' => 0]);
        $sector->handleTranslations(['name_vi' => 'Test Sector Scope 2', 'slug_vi' => 'test-sector-scope-2-'.uniqid()]);

        $parent = ProductCategory::create(['code' => 'test-parent-'.uniqid(), 'is_active' => true]);
        $parent->handleTranslations(['name_vi' => 'Danh muc cha']);

        $sectorChild = ProductCategory::create([
            'code' => 'test-child-sector-'.uniqid(),
            'parent_id' => $parent->id,
            'is_active' => true,
            'display_scopes' => ['homepage' => false, 'sector_ids' => [$sector->id]],
        ]);
        $sectorChild->handleTranslations(['name_vi' => 'Danh muc con nganh test']);

        $homeChild = ProductCategory::create([
            'code' => 'test-child-home-'.uniqid(),
            'parent_id' => $parent->id,
            'is_active' => true,
            'display_scopes' => ['homepage' => true, 'sector_ids' => []],
        ]);
        $homeChild->handleTranslations(['name_vi' => 'Danh muc con trang chu']);

        $categoryService = app(\App\Services\CategoryService::class);

        $sectorScoped = $categoryService->getChildCategories($parent, 'vi', $sector->id);
        $this->assertTrue($sectorScoped->pluck('category_name')->contains('Danh muc con nganh test'));
        $this->assertFalse($sectorScoped->pluck('category_name')->contains('Danh muc con trang chu'));

        $homeScoped = $categoryService->getChildCategories($parent, 'vi', null);
        $this->assertTrue($homeScoped->pluck('category_name')->contains('Danh muc con trang chu'));
        $this->assertFalse($homeScoped->pluck('category_name')->contains('Danh muc con nganh test'));
    }

    public function test_view_service_provider_resolves_current_sector_id_from_sectors_show_route(): void
    {
        [$sector, $slug] = $this->makeSectorAndCategories();

        $route = new Route('GET', '/applications/{slug}', ['as' => 'sectors.show']);
        $route->bind(Request::create('/applications/'.$slug, 'GET'));

        $request = Request::create('/applications/'.$slug, 'GET');
        $request->setRouteResolver(fn () => $route);
        app()->instance('request', $request);

        $provider = new ViewServiceProvider(app());
        $method = new ReflectionMethod($provider, 'currentSectorId');
        $method->setAccessible(true);

        $this->assertSame($sector->id, $method->invoke($provider));
    }

    public function test_view_service_provider_returns_null_sector_id_outside_sector_route(): void
    {
        $route = new Route('GET', '/', ['as' => 'home']);
        $request = Request::create('/', 'GET');
        $request->setRouteResolver(fn () => $route);
        app()->instance('request', $request);

        $provider = new ViewServiceProvider(app());
        $method = new ReflectionMethod($provider, 'currentSectorId');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($provider));
    }
}
