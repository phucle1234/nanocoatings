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

    public function test_sector_crud_controller_wires_contact_social_links_field(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/SectorCrudController.php'));

        $this->assertStringContainsString("CRUD::field('contact_social_links')", $source);
        $this->assertStringContainsString('admin.sector.contact-social-links', $source);
        $this->assertStringContainsString('sectorContactSocialLinks', $source);
    }
}
