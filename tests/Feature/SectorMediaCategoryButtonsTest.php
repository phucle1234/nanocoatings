<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Services\BlockAdminLinkResolver;
use App\Services\SectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SectorMediaCategoryButtonsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_media_block_gets_one_button_per_active_news_category(): void
    {
        app()->setLocale('vi');

        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-media-buttons-'.uniqid();
        $sector->handleTranslations(['name_vi' => 'Test Sector Media Buttons', 'slug_vi' => $slug]);
        $sectorService->syncSectorResources($sector);

        $newsHub = $sectorService->getOrCreateNewsHub($sector);

        // Sector already has its auto-provisioned default "-chung" child.
        // Add 2 more topic categories so the total is 3 — the button count
        // must track however many active categories actually exist, not a
        // hardcoded number.
        foreach (['Kỹ thuật', 'Giải pháp'] as $name) {
            $child = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $newsHub->id,
                'is_active' => true,
                'sort_order' => 5,
            ]);
            $child->handleTranslations(['name_vi' => $name, 'slug_vi' => 'test-'.\Illuminate\Support\Str::slug($name).'-'.uniqid()]);
        }

        $buttons = app(BlockAdminLinkResolver::class)->sectorMediaCategoryButtons($sector);

        $this->assertCount(3, $buttons);
        $labels = collect($buttons)->pluck('label');
        $this->assertTrue($labels->contains('Kỹ thuật'));
        $this->assertTrue($labels->contains('Giải pháp'));

        foreach ($buttons as $button) {
            $this->assertStringContainsString('post?category_id=', $button['url']);
        }
    }

    public function test_media_block_button_count_tracks_deactivating_one_of_several_categories(): void
    {
        app()->setLocale('vi');

        $sectorService = app(SectorService::class);
        $hub = $sectorService->getOrCreateHubCategory();

        $sector = PostCategory::create([
            'parent_id' => $hub->id,
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $slug = 'test-sector-media-buttons-2-'.uniqid();
        $sector->handleTranslations(['name_vi' => 'Test Sector Media Buttons 2', 'slug_vi' => $slug]);
        $sectorService->syncSectorResources($sector);

        $newsHub = $sectorService->getOrCreateNewsHub($sector);

        // Add a 2nd category so deactivating one still leaves an active
        // category behind — deactivating the *only* one would trigger
        // SectorService::ensureNewsCategories()'s own auto-heal (it
        // re-provisions a default "-chung" child whenever a sector has zero
        // active news categories), which is a separate, intentional
        // behavior this test isn't about.
        $extra = PostCategory::withoutGlobalScopes()->create([
            'parent_id' => $newsHub->id,
            'is_active' => true,
            'sort_order' => 5,
        ]);
        $extra->handleTranslations(['name_vi' => 'Giải pháp', 'slug_vi' => 'test-giai-phap-'.uniqid()]);

        $before = app(BlockAdminLinkResolver::class)->sectorMediaCategoryButtons($sector);
        $this->assertCount(2, $before); // auto-provisioned "-chung" + the extra one

        $chung = PostCategory::withoutGlobalScopes()
            ->where('parent_id', $newsHub->id)
            ->where('id', '!=', $extra->id)
            ->first();
        $chung->update(['is_active' => false]);

        $after = app(BlockAdminLinkResolver::class)->sectorMediaCategoryButtons($sector);
        $this->assertCount(1, $after);
        $this->assertSame('Giải pháp', $after[0]['label']);
    }
}
