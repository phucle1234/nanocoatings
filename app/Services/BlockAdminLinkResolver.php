<?php

namespace App\Services;

use App\Models\PostCategory;

class BlockAdminLinkResolver
{
    /**
     * @return array<string, string|null> section_type => admin URL
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
     * Link sửa ảnh nền + tiêu đề/mô tả section (danh mục banner).
     *
     * @return array<string, string|null> section_type => admin URL
     */
    public function sectorBannerMetaLinks(PostCategory $sector): array
    {
        $sectorService = app(SectorService::class);
        $bannerKeys = config('sector_layout.banner_keys', []);
        $links = [];

        foreach (config('sector_layout.blocks', []) as $blockDef) {
            $sectionType = $blockDef['section_type'];

            if (!isset($bannerKeys[$sectionType])) {
                $links[$sectionType] = null;
                continue;
            }

            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$sectionType], 'vi');
            $links[$sectionType] = $this->bannerCategoryEditUrlForSlug($slug);
        }

        return $links;
    }

    /**
     * @return array<string, string|null> section_type => admin URL
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
     */
    protected function resolveSectorBlockLink(
        PostCategory $sector,
        SectorService $sectorService,
        string $sectionType,
        array $bannerKeys
    ): ?string {
        // Nội dung block danh mục / bestseller = SP & danh mục SP, không phải bài viết banner.
        if (in_array($sectionType, ['category', 'bestseller'], true)) {
            return $this->fallbackLink($sectionType, $sector->id);
        }

        if (isset($bannerKeys[$sectionType])) {
            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$sectionType], 'vi');

            return $this->postsUrlForBannerSlug($slug);
        }

        return $this->fallbackLink($sectionType, $sector->id);
    }

    /**
     * @param  array<string, string>  $bannerKeys
     */
    protected function resolveHomepageBlockLink(string $sectionType, array $bannerKeys): ?string
    {
        if (isset($bannerKeys[$sectionType])) {
            return $this->postsUrlForBannerSlug($bannerKeys[$sectionType]);
        }

        return $this->fallbackLink($sectionType);
    }

    protected function postsUrlForBannerSlug(string $slugVi): ?string
    {
        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();

        if (!$category) {
            return null;
        }

        return backpack_url('post?category_id=' . $category->id);
    }

    protected function bannerCategoryEditUrlForSlug(string $slugVi): ?string
    {
        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();

        if (!$category) {
            return null;
        }

        return backpack_url('banner-category/' . $category->id . '/edit');
    }

    protected function fallbackLink(string $sectionType, ?int $sectorId = null): ?string
    {
        $sectorQuery = $sectorId ? '?sector_id=' . $sectorId : '';

        return match ($sectionType) {
            'category' => backpack_url('product-category' . $sectorQuery),
            'bestseller' => backpack_url('product' . $sectorQuery),
            'media' => $this->postsUrlForBannerSlug('truyen-thong')
                ?? backpack_url('post-category'),
            'footer' => $this->postsUrlForBannerSlug('footer-main'),
            default => null,
        };
    }
}
