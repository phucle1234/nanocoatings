<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Collection;

class HomepageLayoutService
{
    public function categorySlug(): string
    {
        return config('homepage_layout.category_slug', 'sap-xep-trang-chu');
    }

    public function postType(): string
    {
        return config('homepage_layout.post_type', 'homepage_block');
    }

    /**
     * @return array<int, string>
     */
    public function allowedSectionTypes(): array
    {
        return array_column(config('homepage_layout.blocks', []), 'section_type');
    }

    public function isLayoutCategory(?PostCategory $category): bool
    {
        if (!$category) {
            return false;
        }

        $slugs = array_filter([
            $this->categorySlug(),
            config('homepage_layout.category_slug_en'),
        ]);

        return $category->translations()
            ->whereIn('slug', $slugs)
            ->exists();
    }

    public function isLayoutCategoryId(int $categoryId): bool
    {
        $category = PostCategory::find($categoryId);

        return $this->isLayoutCategory($category);
    }

    public function isLayoutPost(?Post $post): bool
    {
        return $post && $post->post_type === $this->postType();
    }

    public function getOrCreateLayoutCategory(): ?PostCategory
    {
        $slug = $this->categorySlug();
        $category = PostCategory::whereHas('translations', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->first();

        if ($category) {
            return $category;
        }

        $category = PostCategory::create([
            'parent_id' => null,
            'is_active' => true,
            'is_featured' => false,
            'is_banner' => false,
            'sort_order' => 999,
        ]);

        $category->handleTranslations([
            'name_vi' => 'Sắp xếp trang chủ',
            'name_en' => 'Homepage layout',
            'slug_vi' => config('homepage_layout.category_slug'),
            'slug_en' => config('homepage_layout.category_slug_en'),
            'description_vi' => 'Cấu hình thứ tự các block trên trang chủ',
            'description_en' => 'Homepage section order configuration',
        ]);

        return $category;
    }

    public function ensureDefaultBlocks(): void
    {
        $category = $this->getOrCreateLayoutCategory();
        if (!$category) {
            return;
        }

        foreach (config('homepage_layout.blocks', []) as $blockDef) {
            $sectionType = $blockDef['section_type'];
            $existing = Post::withoutGlobalScopes()
                ->where('post_type', $this->postType())
                ->where('section_type', $sectionType)
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
                'slug_vi' => 'block-' . $sectionType,
                'slug_en' => 'block-' . $sectionType,
            ]);
        }
    }

    /**
     * All layout blocks for admin (including inactive).
     *
     * @return Collection<int, Post>
     */
    public function getLayoutBlocksForAdmin(): Collection
    {
        $this->ensureDefaultBlocks();

        $category = $this->getOrCreateLayoutCategory();
        if (!$category) {
            return collect();
        }

        return Post::withoutGlobalScopes()
            ->where('post_type', $this->postType())
            ->whereHas('postcategories', function ($q) use ($category) {
                $q->where('postcategories.id', $category->id);
            })
            ->with(['translations'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Active blocks for homepage render.
     *
     * @return Collection<int, Post>
     */
    public function getActiveLayoutBlocks(): Collection
    {
        $this->ensureDefaultBlocks();

        return $this->getLayoutBlocksForAdmin()
            ->where('is_active', true)
            ->values();
    }

    /**
     * @param  array<int, array{id: int, is_active?: bool}>  $items
     */
    public function saveOrderAndStatus(array $items): void
    {
        $allowedIds = $this->getLayoutBlocksForAdmin()->pluck('id')->all();

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
