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

            if (! isset($bannerKeys[$sectionType])) {
                $links[$sectionType] = null;

                continue;
            }

            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$sectionType], 'vi');
            $links[$sectionType] = $this->bannerCategoryEditUrlForSlug($slug);
        }

        return $links;
    }

    /**
     * Quick links to this sector's own contact-info / about / social-links
     * banner categories (footer_contact / footer_about / footer_social) —
     * these aren't page "blocks" (not in sector_layout.blocks), just banner
     * categories keyed in sector_layout.banner_keys, so shown directly on
     * the Sector edit form instead of the block-management page.
     *
     * @return array<string, string|null> banner_keys key => admin URL
     */
    public function sectorContactSocialLinks(PostCategory $sector): array
    {
        $sectorService = app(SectorService::class);
        $bannerKeys = config('sector_layout.banner_keys', []);
        $keys = ['footer_contact', 'footer_about', 'footer_social'];
        $links = [];

        foreach ($keys as $key) {
            if (! isset($bannerKeys[$key])) {
                $links[$key] = null;

                continue;
            }

            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$key], 'vi');
            $links[$key] = $this->postsUrlForBannerSlug($slug);
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
        // Nội dung block danh mục / bestseller / media = quản lý riêng theo ngành.
        // Footer ngành chỉ đổi ảnh nền; nội dung dùng chung footer trang chủ.
        if (in_array($sectionType, ['category', 'bestseller', 'media'], true)) {
            return $this->fallbackLink($sectionType, $sector->id);
        }

        if ($sectionType === 'footer') {
            return null;
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
        // Media / Footer: quản lý nội dung ở menu riêng, không qua layout trang chủ.
        if (in_array($sectionType, ['media', 'footer'], true)) {
            return null;
        }

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

        if (! $category) {
            return null;
        }

        return backpack_url('post?category_id='.$category->id);
    }

    protected function bannerCategoryEditUrlForSlug(string $slugVi): ?string
    {
        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();

        if (! $category) {
            return null;
        }

        return backpack_url('banner-category/'.$category->id.'/edit');
    }

    protected function fallbackLink(string $sectionType, ?int $sectorId = null): ?string
    {
        $sectorQuery = $sectorId ? '?sector_id='.$sectorId : '';

        return match ($sectionType) {
            'category' => backpack_url('product-category'.$sectorQuery),
            'bestseller' => backpack_url('product'.$sectorQuery),
            'media' => $sectorId
                ? $this->sectorNewsContentUrl($sectorId)
                : ($this->postsUrlForBannerSlug('truyen-thong') ?? backpack_url('post-category')),
            'footer' => $this->postsUrlForBannerSlug('footer-main'),
            default => null,
        };
    }

    protected function sectorNewsContentUrl(int $sectorId): ?string
    {
        $sector = PostCategory::withoutGlobalScopes()
            ->where('id', $sectorId)
            ->where('is_sector', true)
            ->with('translations')
            ->first();

        if (! $sector) {
            return null;
        }

        $sectorService = app(SectorService::class);
        $sectorService->syncNewsCategories($sector);
        $hub = $sectorService->getOrCreateNewsHub($sector);

        if (! $hub) {
            return null;
        }

        $tabCategory = PostCategory::withoutGlobalScopes()
            ->where('parent_id', $hub->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $categoryId = $tabCategory?->id ?? $hub->id;

        return backpack_url('post?category_id='.$categoryId);
    }
}
