<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Collection;

class SectorLayoutService
{
    public function postType(): string
    {
        return config('sector_layout.post_type', 'sector_block');
    }

    /**
     * @return array<int, string>
     */
    public function allowedSectionTypes(): array
    {
        return array_column(config('sector_layout.blocks', []), 'section_type');
    }

    public function layoutCategorySlug(PostCategory $sector): string
    {
        $sectorService = app(SectorService::class);
        $sectorSlug = $sectorService->getSectorSlug($sector, 'vi');

        return 'sector-' . $sectorSlug . '-layout';
    }

    public function getOrCreateLayoutCategory(PostCategory $sector): ?PostCategory
    {
        $slug = $this->layoutCategorySlug($sector);

        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();

        if ($category) {
            return $category;
        }

        $sectorNameVi = $sector->translations->firstWhere('language', 'vi')->name ?? $slug;
        $sectorNameEn = $sector->translations->firstWhere('language', 'en')->name ?? $slug;

        $category = PostCategory::withoutGlobalScopes()->create([
            'parent_id' => $sector->id,
            'is_active' => true,
            'is_featured' => false,
            'is_banner' => false,
            'is_sector' => false,
            'sort_order' => 99,
        ]);

        $category->handleTranslations([
            'name_vi' => 'Sắp xếp trang - ' . $sectorNameVi,
            'name_en' => 'Page layout - ' . $sectorNameEn,
            'slug_vi' => $slug,
            'slug_en' => $slug . '-en',
            'description_vi' => 'Cấu hình block trang ngành',
            'description_en' => 'Sector page block configuration',
        ]);

        return $category;
    }

    public function ensureDefaultBlocks(PostCategory $sector): void
    {
        $category = $this->getOrCreateLayoutCategory($sector);
        if (!$category) {
            return;
        }

        foreach (config('sector_layout.blocks', []) as $blockDef) {
            $sectionType = $blockDef['section_type'];

            $existing = Post::withoutGlobalScopes()
                ->where('post_type', $this->postType())
                ->where('section_type', $sectionType)
                ->whereHas('postcategories', fn ($q) => $q->where('postcategories.id', $category->id))
                ->first();

            if ($existing) {
                if (!$existing->postcategories()->where('postcategories.id', $category->id)->exists()) {
                    $existing->postcategories()->attach($category->id, [
                        'is_primary' => true,
                        'sort_order' => $blockDef['sort_order'],
                    ]);
                }
                continue;
            }

            $post = Post::withoutGlobalScopes()->create([
                'post_type' => $this->postType(),
                'section_type' => $sectionType,
                'status' => 'published',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $blockDef['sort_order'],
                'published_at' => now(),
            ]);

            $post->postcategories()->attach($category->id, [
                'is_primary' => true,
                'sort_order' => $blockDef['sort_order'],
            ]);

            $post->handleTranslations([
                'title_vi' => $blockDef['title_vi'],
                'title_en' => $blockDef['title_en'],
                'slug_vi' => 'sector-block-' . $sectionType . '-' . $sector->id,
                'slug_en' => 'sector-block-' . $sectionType . '-' . $sector->id . '-en',
            ]);
        }
    }

    /**
     * @return Collection<int, Post>
     */
    public function getLayoutBlocksForAdmin(PostCategory $sector): Collection
    {
        $this->ensureDefaultBlocks($sector);

        $category = $this->getOrCreateLayoutCategory($sector);
        if (!$category) {
            return collect();
        }

        return Post::withoutGlobalScopes()
            ->where('post_type', $this->postType())
            ->whereHas('postcategories', fn ($q) => $q->where('postcategories.id', $category->id))
            ->with(['translations'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, Post>
     */
    public function getActiveLayoutBlocks(PostCategory $sector): Collection
    {
        return $this->getLayoutBlocksForAdmin($sector)
            ->where('is_active', true)
            ->values();
    }

    /**
     * @param  array<int, array{id: int, is_active?: bool}>  $items
     */
    public function saveOrderAndStatus(PostCategory $sector, array $items): void
    {
        $allowedIds = $this->getLayoutBlocksForAdmin($sector)->pluck('id')->all();

        foreach ($items as $position => $item) {
            $id = (int) ($item['id'] ?? 0);
            if (!in_array($id, $allowedIds, true)) {
                continue;
            }

            Post::withoutGlobalScopes()
                ->where('id', $id)
                ->where('post_type', $this->postType())
                ->update([
                    'sort_order' => $position + 1,
                    'is_active' => !empty($item['is_active']),
                ]);
        }
    }

    public function blockViewExists(string $sectionType): bool
    {
        return in_array($sectionType, $this->allowedSectionTypes(), true)
            && view()->exists('langding.home.blocks.' . $sectionType);
    }
}
