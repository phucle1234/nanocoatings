<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasBanners
{
    /**
     * Lấy banners từ danh mục theo slug
     *
     * @param string $categorySlug Slug của danh mục (ví dụ: 'home-slider', 'home-slider-en')
     * @param bool $requireImage When false, include text-only items (e.g. footer menu links)
     * @return array ['category' => object, 'category_bg_image' => string, 'banners' => Collection]
     */
    public function getBannersBySlug($categorySlug, bool $requireImage = true)
    {
        $currentLocale = app()->getLocale();
        $fallbackLocale = $this->resolveBannerFallbackLocale($currentLocale);

        // Tìm danh mục theo slug ở bất kỳ ngôn ngữ nào (vi: home-slider, en: home-slider-en, ...)
        $categoryTranslation = DB::table('postcategory_translations')
            ->where('slug', $categorySlug)
            ->first();

        if (!$categoryTranslation) {
            return [
                'category' => null,
                'category_bg_image' => null,
                'category_all_bg_images' => [],
                'banners' => collect([]),
            ];
        }

        // Lấy thông tin danh mục theo locale hiện tại (fallback sang ngôn ngữ còn lại)
        $category = DB::table('postcategories as pc')
            ->leftJoin('postcategory_translations as pct_current', function ($join) use ($currentLocale) {
                $join->on('pc.id', '=', 'pct_current.postcategory_id')
                    ->where('pct_current.language', '=', $currentLocale);
            })
            ->leftJoin('postcategory_translations as pct_fallback', function ($join) use ($fallbackLocale) {
                $join->on('pc.id', '=', 'pct_fallback.postcategory_id')
                    ->where('pct_fallback.language', '=', $fallbackLocale);
            })
            ->where('pc.id', $categoryTranslation->postcategory_id)
            ->where('pc.is_active', true)
            ->select(
                'pc.*',
                DB::raw('COALESCE(pct_current.name, pct_fallback.name) as category_name'),
                DB::raw('COALESCE(pct_current.description, pct_fallback.description) as category_description'),
                'pct_current.image_urls as category_image_urls_current',
                'pct_fallback.image_urls as category_image_urls_fallback',
                DB::raw('COALESCE(pct_current.meta_title, pct_fallback.meta_title) as meta_title'),
                DB::raw('COALESCE(pct_current.meta_description, pct_fallback.meta_description) as meta_description'),
                DB::raw('COALESCE(pct_current.slug, pct_fallback.slug) as slug')
            )
            ->first();

        if (!$category) {
            return [
                'category' => null,
                'category_bg_image' => null,
                'category_all_bg_images' => [],
                'banners' => collect([]),
            ];
        }

        $banners = DB::table('posts as p')
            ->leftJoin('post_translations as pt_current', function ($join) use ($currentLocale) {
                $join->on('p.id', '=', 'pt_current.post_id')
                    ->where('pt_current.language', '=', $currentLocale);
            })
            ->leftJoin('post_translations as pt_fallback', function ($join) use ($fallbackLocale) {
                $join->on('p.id', '=', 'pt_fallback.post_id')
                    ->where('pt_fallback.language', '=', $fallbackLocale);
            })
            ->join('post_postcategory as ppc', 'p.id', '=', 'ppc.post_id')
            ->where('ppc.postcategory_id', $category->id)
            ->where('p.is_active', true)
            ->where(function ($query) {
                $query->whereNotNull('pt_current.id')
                    ->orWhereNotNull('pt_fallback.id');
            })
            ->select(
                'p.*',
                DB::raw('COALESCE(pt_current.title, pt_fallback.title) as title'),
                DB::raw('COALESCE(pt_current.content, pt_fallback.content) as content'),
                DB::raw('COALESCE(pt_current.excerpt, pt_fallback.excerpt) as excerpt'),
                DB::raw('COALESCE(pt_current.meta_description, pt_fallback.meta_description) as meta_description'),
                DB::raw('COALESCE(pt_current.canonical_url, pt_fallback.canonical_url) as canonical_url'),
                DB::raw('COALESCE(pt_current.slug, pt_fallback.slug) as slug'),
                'pt_current.image_urls as image_urls_current',
                'pt_fallback.image_urls as image_urls_fallback',
                DB::raw('COALESCE(pt_current.url, pt_fallback.url) as url'),
                DB::raw('COALESCE(pt_current.meta_title, pt_fallback.meta_title) as meta_title')
            )
            ->distinct()
            ->orderBy('p.sort_order')
            ->get()
            ->map(function ($banner) use ($requireImage) {
                $effectiveImageUrls = $this->resolveEffectiveBannerImageUrls(
                    $banner->image_urls_current,
                    $banner->image_urls_fallback
                );

                $hasImage = $this->imageUrlListHasResolvableFile($effectiveImageUrls);

                if ($requireImage && !$hasImage) {
                    return null;
                }

                if ($hasImage) {
                    $banner->image = $this->getImageJson($effectiveImageUrls);
                    $banner->all_images = $this->getAllImagesJson($effectiveImageUrls);
                } else {
                    $banner->image = null;
                    $banner->all_images = [];
                }

                return $banner;
            })
            ->filter()
            ->values();

        $categoryBgImage = $this->getImageJson(
            $this->resolveEffectiveBannerImageUrls(
                $category->category_image_urls_current ?? null,
                $category->category_image_urls_fallback ?? null
            )
        );

        return [
            'category' => $category,
            'category_bg_image' => $categoryBgImage,
            'banners' => $banners,
        ];
    }

    /**
     * Ngôn ngữ dự phòng khi thiếu bản dịch (luôn khác locale hiện tại nếu có thể).
     */
    protected function resolveBannerFallbackLocale(string $currentLocale): string
    {
        $supported = array_keys(config('languages.supported', ['en' => 'English', 'vi' => 'Tiếng Việt']));

        foreach ($supported as $code) {
            if ($code !== $currentLocale) {
                return $code;
            }
        }

        $configuredFallback = config('app.fallback_locale');

        return ($configuredFallback && $configuredFallback !== $currentLocale)
            ? $configuredFallback
            : 'en';
    }

    /**
     * Prefer current-locale images when files exist; otherwise use alternate locale.
     *
     * @param  mixed  $currentUrls
     * @param  mixed  $fallbackUrls
     * @return array<int, string>
     */
    protected function resolveEffectiveBannerImageUrls($currentUrls, $fallbackUrls): array
    {
        $candidates = [
            $this->normalizeBannerImageUrlList($currentUrls),
            $this->normalizeBannerImageUrlList($fallbackUrls),
        ];

        foreach ($candidates as $urls) {
            if ($this->imageUrlListHasResolvableFile($urls)) {
                return $urls;
            }
        }

        return [];
    }

    /**
     * @param  mixed  $imageUrls
     * @return array<int, string>
     */
    protected function normalizeBannerImageUrlList($imageUrls): array
    {
        if ($imageUrls === null || $imageUrls === '') {
            return [];
        }

        if (is_string($imageUrls)) {
            $decoded = json_decode($imageUrls, true);
            $imageUrls = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($imageUrls)) {
            return [];
        }

        return array_values(array_filter($imageUrls, static function ($url) {
            return $url !== null && $url !== '';
        }));
    }
}
