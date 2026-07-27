<?php

namespace App\Services;

use App\Models\PostCategory;
use Illuminate\Support\Collection;

class SectorService
{
    public function __construct(
        protected SectorLayoutService $sectorLayoutService
    ) {
    }

    public function hubSlug(): string
    {
        return config('sector_layout.hub_slug', 'nganh-ung-dung');
    }

    public function getOrCreateHubCategory(): ?PostCategory
    {
        $slug = $this->hubSlug();
        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();

        if ($category) {
            return $category;
        }

        $category = PostCategory::withoutGlobalScopes()->create([
            'parent_id' => null,
            'is_active' => true,
            'is_featured' => false,
            'is_banner' => false,
            'is_sector' => false,
            'sort_order' => 50,
        ]);

        $category->handleTranslations([
            'name_vi' => 'Ngành ứng dụng',
            'name_en' => 'Application sectors',
            'slug_vi' => config('sector_layout.hub_slug'),
            'slug_en' => config('sector_layout.hub_slug_en'),
            'description_vi' => 'Danh sách các ngành ứng dụng',
            'description_en' => 'List of application sectors',
        ]);

        return $category;
    }

    /**
     * @return Collection<int, PostCategory>
     */
    public function getActiveSectors(): Collection
    {
        $hub = $this->getOrCreateHubCategory();
        if (!$hub) {
            return collect();
        }

        return PostCategory::withoutGlobalScopes()
            ->where('parent_id', $hub->id)
            ->where('is_sector', true)
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function findSectorBySlug(string $slug): ?PostCategory
    {
        $hub = $this->getOrCreateHubCategory();
        if (!$hub) {
            return null;
        }

        return PostCategory::withoutGlobalScopes()
            ->where('parent_id', $hub->id)
            ->where('is_sector', true)
            ->where('is_active', true)
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->with('translations')
            ->first();
    }

    public function getSectorSlug(PostCategory $sector, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $sector->translations->firstWhere('language', $locale)
            ?? $sector->translations->firstWhere('language', 'vi')
            ?? $sector->translations->first();

        return $translation->slug ?? '';
    }

    /**
     * Slug danh mục banner theo ngành, ví dụ: sector-vat-lieu-go-home-slider
     */
    public function getBannerCategorySlug(PostCategory $sector, string $bannerKey, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $sectorSlug = $this->getSectorSlug($sector, 'vi');
        $prefix = config('sector_layout.banner_prefix', 'sector');

        $base = $prefix . '-' . $sectorSlug . '-' . $bannerKey;

        if ($locale === 'en') {
            return $base . '-en';
        }

        return $base;
    }

    /**
     * Tạo danh mục layout + banner + block mặc định cho ngành mới.
     */
    public function provisionSector(PostCategory $sector): void
    {
        $this->sectorLayoutService->ensureDefaultBlocks($sector);

        $this->syncSectorResources($sector);
    }

    /**
     * Đồng bộ banner + danh mục tin tức theo ngành (gọi khi mở layout/trang ngành).
     */
    public function syncSectorResources(PostCategory $sector): void
    {
        $this->syncBannerCategories($sector);
        $this->syncNewsCategories($sector);
    }

    /**
     * Tạo danh mục banner còn thiếu (gọi khi thêm banner_keys mới hoặc mở layout/trang ngành).
     */
    public function syncBannerCategories(PostCategory $sector): void
    {

        $this->ensureBannerCategories($sector);
    }

    /**
     * Tạo hub tin tức riêng theo ngành (không dùng chung truyen-thong).
     */
    public function syncNewsCategories(PostCategory $sector): void
    {
        $this->ensureNewsCategories($sector);
    }

    /**
     * Slug hub tin tức theo ngành, ví dụ: sector-vat-lieu-go-news
     */
    public function getNewsHubSlug(PostCategory $sector, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $sectorSlug = $this->getSectorSlug($sector, 'vi');
        $prefix = config('sector_layout.banner_prefix', 'sector');
        $base = $prefix . '-' . $sectorSlug . '-news';

        if ($locale === 'en') {
            return $base . '-en';
        }

        return $base;
    }

    public function getOrCreateNewsHub(PostCategory $sector): ?PostCategory
    {
        $slugVi = $this->getNewsHubSlug($sector, 'vi');

        return PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();
    }

    protected function ensureNewsCategories(PostCategory $sector): void
    {
        $sectorSlug = $this->getSectorSlug($sector, 'vi');
        $prefix = config('sector_layout.banner_prefix', 'sector');
        $hubSlugVi = $prefix . '-' . $sectorSlug . '-news';

        $hub = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $hubSlugVi))
            ->first();

        if (!$hub) {
            $sectorNameVi = $sector->translations->firstWhere('language', 'vi')->name ?? $sectorSlug;
            $sectorNameEn = $sector->translations->firstWhere('language', 'en')->name ?? $sectorSlug;

            $hub = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $sector->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => false,
                'is_sector' => false,
                'sort_order' => 20,
            ]);

            $hub->handleTranslations([
                'name_vi' => 'Tin tức - ' . $sectorNameVi,
                'name_en' => 'News - ' . $sectorNameEn,
                'slug_vi' => $hubSlugVi,
                'slug_en' => $hubSlugVi . '-en',
                'description_vi' => 'Danh mục tin tức riêng cho ngành ' . $sectorNameVi,
                'description_en' => 'News categories for the ' . $sectorNameEn . ' sector',
            ]);
        }

        $hasChildren = PostCategory::withoutGlobalScopes()
            ->where('parent_id', $hub->id)
            ->where('is_active', true)
            ->exists();

        if (!$hasChildren) {
            $childSlugVi = $hubSlugVi . '-chung';
            $child = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $hub->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => false,
                'is_sector' => false,
                'sort_order' => 1,
            ]);

            $child->handleTranslations([
                'name_vi' => 'Tin tức chung',
                'name_en' => 'General news',
                'slug_vi' => $childSlugVi,
                'slug_en' => $childSlugVi . '-en',
            ]);
        }
    }
    
    protected function ensureBannerCategories(PostCategory $sector): void
    {
        $sectorSlug = $this->getSectorSlug($sector, 'vi');
        $prefix = config('sector_layout.banner_prefix', 'sector');
        $bannerParentSlug = $prefix . '-' . $sectorSlug . '-banners';

        $bannerParent = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $bannerParentSlug))
            ->first();

        if (!$bannerParent) {
            $bannerParent = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $sector->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => true,
                'is_sector' => false,
                'sort_order' => 1,
            ]);

            $bannerParent->handleTranslations([
                'name_vi' => 'Banner - ' . ($sector->translations->firstWhere('language', 'vi')->name ?? $sectorSlug),
                'name_en' => 'Banners - ' . ($sector->translations->firstWhere('language', 'en')->name ?? $sectorSlug),
                'slug_vi' => $bannerParentSlug,
                'slug_en' => $bannerParentSlug . '-en',
            ]);
        }

        foreach (config('sector_layout.banner_keys', []) as $sectionType => $bannerKey) {
            $slugVi = $prefix . '-' . $sectorSlug . '-' . $bannerKey;
            $existing = PostCategory::withoutGlobalScopes()
                ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
                ->first();

            if ($existing) {
                continue;
            }

            $blockDef = collect(config('sector_layout.blocks', []))
                ->firstWhere('section_type', $sectionType);

            $labelVi = $blockDef['title_vi'] ?? $bannerKey;
            $labelEn = $blockDef['title_en'] ?? $bannerKey;

            $bannerCategory = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $bannerParent->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => true,
                'is_sector' => false,
                'sort_order' => 1,
            ]);

            $bannerCategory->handleTranslations([
                'name_vi' => $labelVi . ' (' . ($sector->translations->firstWhere('language', 'vi')->name ?? $sectorSlug) . ')',
                'name_en' => $labelEn . ' (' . ($sector->translations->firstWhere('language', 'en')->name ?? $sectorSlug) . ')',
                'slug_vi' => $slugVi,
                'slug_en' => $slugVi . '-en',
            ]);
        }
    }
}
