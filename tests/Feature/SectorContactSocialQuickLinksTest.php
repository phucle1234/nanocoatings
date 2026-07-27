<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Services\BlockAdminLinkResolver;
use App\Services\SectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SectorContactSocialQuickLinksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sector_contact_social_links_point_to_that_sectors_own_banner_category_posts(): void
    {
        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $sector->handleTranslations([
            'name_vi' => 'Test Sector Quick Links',
            'slug_vi' => 'test-sector-quick-links-'.uniqid(),
        ]);

        $sectorService->syncSectorResources($sector);

        $links = app(BlockAdminLinkResolver::class)->sectorContactSocialLinks($sector);

        foreach (['footer_contact', 'footer_about', 'footer_social'] as $key) {
            $this->assertNotNull($links[$key], "Expected a resolved admin link for [{$key}]");

            $bannerKey = config('sector_layout.banner_keys')[$key];
            $expectedSlug = $sectorService->getBannerCategorySlug($sector, $bannerKey, 'vi');
            $category = PostCategory::withoutGlobalScopes()
                ->whereHas('translations', fn ($q) => $q->where('slug', $expectedSlug))
                ->first();

            $this->assertNotNull($category, "Expected banner category [{$expectedSlug}] to have been provisioned.");
            $this->assertStringContainsString('category_id='.$category->id, $links[$key]);
        }
    }

    public function test_contact_social_links_view_exists(): void
    {
        $this->assertTrue(view()->exists('admin.sector.contact-social-links'));
    }

    public function test_view_renders_clickable_link_when_backpack_passes_data_via_field_data_key(): void
    {
        // Backpack's CrudField::__call() (see vendor CrudField.php ~line 487,
        // setAttributeValue) stores ->data([...]) under $field['data'], NOT
        // merged as top-level $field keys — CRUD::field(...)->data(['links' => ...])
        // means the real shape at render time is $field['data']['links'], never
        // $field['links'] directly. Reproduces the "buttons show but are
        // disabled/unclickable" bug caused by reading the wrong key.
        $links = [
            'footer_contact' => '/admin/post?category_id=1',
            'footer_about' => '/admin/post?category_id=2',
            'footer_social' => '/admin/post?category_id=3',
        ];

        $view = $this->view('admin.sector.contact-social-links', [
            'field' => ['data' => ['links' => $links]],
        ]);

        $view->assertSee('/admin/post?category_id=1', false);
        $view->assertDontSee('disabled', false);
    }

    public function test_view_renders_disabled_buttons_when_links_are_missing(): void
    {
        $view = $this->view('admin.sector.contact-social-links', [
            'field' => ['data' => ['links' => []]],
        ]);

        $view->assertSee('disabled', false);
    }

    public function test_sector_crud_controller_wires_contact_social_links_field(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/SectorCrudController.php'));

        $this->assertStringContainsString("CRUD::field('contact_social_links')", $source);
        $this->assertStringContainsString('admin.sector.contact-social-links', $source);
        $this->assertStringContainsString('sectorContactSocialLinks', $source);
    }
}
