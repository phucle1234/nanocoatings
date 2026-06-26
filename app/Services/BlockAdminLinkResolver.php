<?php

namespace App\Services;

use App\Models\PostCategory;

class BlockAdminLinkResolver
{
    /**
     * @return array<string, array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}>
     */
    public function sectorBlockLinks(PostCategory $sector): array
    {
        $sectorService = app(SectorService::class);
        $bannerKeys = config('sector_layout.banner_keys', []);
        $links = [];

        foreach (config('sector_layout.blocks', []) as $blockDef) {
            $sectionType = $blockDef['section_type'];
            $links[$sectionType] = $this->resolveSectorBlockLink(
                $sector,
                $sectorService,
                $sectionType,
                $bannerKeys
            );
        }

        return $links;
    }

    /**
     * @return array<string, array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}>
     */
    public function homepageBlockLinks(): array
    {
        $bannerKeys = config('homepage_layout.banner_keys', []);
        $links = [];

        foreach (config('homepage_layout.blocks', []) as $blockDef) {
            $sectionType = $blockDef['section_type'];
            $links[$sectionType] = $this->resolveHomepageBlockLink($sectionType, $bannerKeys);
        }

        return $links;
    }

    /**
     * @param  array<string, string>  $bannerKeys
     * @return array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}
     */
    protected function resolveSectorBlockLink(
        PostCategory $sector,
        SectorService $sectorService,
        string $sectionType,
        array $bannerKeys
    ): array {
        if (isset($bannerKeys[$sectionType])) {
            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$sectionType], 'vi');

            return $this->bannerBlockLinks($slug);
        }

        return $this->fallbackLink($sectionType);
    }

    /**
     * @param  array<string, string>  $bannerKeys
     * @return array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}
     */
    protected function resolveHomepageBlockLink(string $sectionType, array $bannerKeys): array
    {
        if (isset($bannerKeys[$sectionType])) {
            return $this->bannerBlockLinks($bannerKeys[$sectionType]);
        }

        return $this->fallbackLink($sectionType);
    }

    /**
     * @return array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}
     */
    protected function bannerBlockLinks(string $slugVi): array
    {
        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();

        if (!$category) {
            return ['posts' => null, 'category' => null, 'manage' => null, 'manage_label' => null];
        }

        return [
            'posts' => backpack_url('post?category_id=' . $category->id),
            'category' => backpack_url('banner-category/' . $category->id . '/edit'),
            'manage' => null,
            'manage_label' => null,
        ];
    }

    /**
     * @return array{posts: ?string, category: ?string, manage: ?string, manage_label: ?string}
     */
    protected function fallbackLink(string $sectionType): array
    {
        $manage = match ($sectionType) {
            'category' => backpack_url('product-category'),
            'bestseller' => backpack_url('product'),
            'media' => $this->bannerBlockLinks('truyen-thong')['posts'] ?? backpack_url('post-category'),
            default => null,
        };

        $label = match ($sectionType) {
            'category' => 'Danh mục sản phẩm',
            'bestseller' => 'Sản phẩm',
            'media' => 'Tin tức',
            default => 'Quản lý',
        };

        if ($sectionType === 'media' && $manage) {
            return ['posts' => $manage, 'category' => null, 'manage' => null, 'manage_label' => null];
        }

        return [
            'posts' => null,
            'category' => null,
            'manage' => $manage,
            'manage_label' => $label,
        ];
    }
}
