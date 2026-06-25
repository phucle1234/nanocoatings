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
        if (isset($bannerKeys[$sectionType])) {
            $slug = $sectorService->getBannerCategorySlug($sector, $bannerKeys[$sectionType], 'vi');

            return $this->postsUrlForBannerSlug($slug);
        }

        return $this->fallbackLink($sectionType);
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

    protected function fallbackLink(string $sectionType): ?string
    {
        return match ($sectionType) {
            'category' => backpack_url('product-category'),
            'bestseller' => backpack_url('product'),
            'media' => $this->postsUrlForBannerSlug('truyen-thong')
                ?? backpack_url('post-category'),
            'footer' => $this->postsUrlForBannerSlug('footer-main'),
            default => null,
        };
    }
}
