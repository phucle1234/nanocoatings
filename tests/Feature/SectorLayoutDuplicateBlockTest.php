<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SectorLayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SectorLayoutDuplicateBlockTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ensure_default_blocks_merges_pre_existing_duplicate_blocks(): void
    {
        $sector = PostCategory::create([
            'is_active' => true,
            'is_sector' => true,
            'sort_order' => 0,
        ]);
        $sector->handleTranslations([
            'name_vi' => 'Test sector dup',
            'slug_vi' => 'test-sector-dup-'.uniqid(),
        ]);
        $sector->refresh();

        $service = app(SectorLayoutService::class);
        $layoutCategory = $service->getOrCreateLayoutCategory($sector);

        // Simulate a past race condition: two Post rows for the same
        // section_type already attached to this sector's layout category
        // (this is exactly the "2 application_slider_2 blocks on one page"
        // bug report — no unique constraint stopped it from happening).
        $sectionType = 'application_slider_2';

        $first = Post::create([
            'post_type' => $service->postType(),
            'section_type' => $sectionType,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $first->postcategories()->attach($layoutCategory->id, ['is_primary' => true, 'sort_order' => 2]);

        $second = Post::create([
            'post_type' => $service->postType(),
            'section_type' => $sectionType,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $second->postcategories()->attach($layoutCategory->id, ['is_primary' => true, 'sort_order' => 2]);

        $service->ensureDefaultBlocks($sector);

        $remaining = Post::withoutGlobalScopes()
            ->where('post_type', $service->postType())
            ->where('section_type', $sectionType)
            ->whereHas('postcategories', fn ($q) => $q->where('postcategories.id', $layoutCategory->id))
            ->get();

        $this->assertCount(1, $remaining);
        $this->assertSame($first->id, $remaining->first()->id);
    }
}
