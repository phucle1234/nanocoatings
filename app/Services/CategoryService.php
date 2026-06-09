<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;
use Illuminate\Support\Collection;

class CategoryService
{
    /**
     * Lấy category theo slug hoặc ID
     * 
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @param string $locale Locale hiện tại
     * @return ProductCategory|null
     */

    /**
     * Lấy danh sách danh mục gốc (parent_id = null hoặc 0)
     *
     * @param string|null $locale
     * @return Collection
     */
    public function getRootCategories(?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return ProductCategory::query()
            ->where('is_active', true)
            ->root() // Sử dụng scope root() để lấy parent_id = null
            ->withCount('productsManyToMany as products_count')
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale) {
                return $this->formatCategoryForFrontend($category, $locale);
            });
    }
    public function getCategoryBySlugOrId($identifier, ?string $locale = null): ?ProductCategory
    {
        // Lấy locale từ app nếu không được truyền vào
        if ($locale === null) {
            $locale = app()->getLocale();
        }
        // Thử tìm theo ID trước
        if (is_numeric($identifier)) {
            $category = ProductCategory::with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
                ->withCount('productsManyToMany as products_count') // ✅ Fix N+1 query
                ->find($identifier);

            if ($category) {
                return $category;
            }
        }

        // Tìm theo slug trong translations sử dụng Eloquent
        $categoryTranslation = ProductCategoryTranslation::where('slug', $identifier)
            ->where('language', $locale)
            ->first();

        if ($categoryTranslation) {
            return ProductCategory::with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
                ->withCount('productsManyToMany as products_count') // ✅ Fix N+1 query
                ->find($categoryTranslation->category_id);
        }

        return null;
    }

    /**
     * Lấy danh sách danh mục nổi bật
     * 
     * @param string|null $locale
     * @return Collection
     */
    public function getFeaturedCategories(?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return ProductCategory::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->withCount('productsManyToMany as products_count') // ✅ Fix N+1 query
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale) {
                return $this->formatCategoryForFrontend($category, $locale);
            });
    }

    /**
     * Lấy tất cả danh mục đang hoạt động
     * 
     * @param string|null $locale
     * @return Collection
     */
    public function getAllActiveCategories(?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return ProductCategory::query()
            ->where('is_active', true)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->withCount('productsManyToMany as products_count') // ✅ Fix N+1 query
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale) {
                return $this->formatCategoryForFrontend($category, $locale);
            });
    }

    /**
     * Format category data cho frontend (Blade views)
     * 
     * @param ProductCategory $category
     * @param string $locale
     * @return object
     */

    private function formatCategoryForFrontend(ProductCategory $category, string $locale): object
    {
        $translation = $category->translations->firstWhere('language', $locale);

        $category->category_name = $translation->name ?? null;
        $category->category_description = $translation->description ? strip_tags($translation->description) : null;
        $category->category_description_html = $translation->description ?? null;
        $category->category_meta_title = $translation->meta_title ?? null;
        $category->category_meta_description = $translation->meta_description ?? null;
        $category->category_translation_slug = $translation->slug ?? null;
        $category->category_image_urls = $translation->image_urls ?? null;
        $category->link_type = $translation->link_type ?? 'detail';
        $category->youtube_url = $translation->youtube_url ?? null;

        return $category;
    }

    /**
     * Lấy products_count từ category (đảm bảo luôn là integer)
     * 
     * @param ProductCategory $category
     * @return int
     */
    public function getProductsCount(ProductCategory $category): int
    {
        // Nếu chưa có products_count, load nó
        if (!isset($category->products_count)) {
            $category->loadCount('productsManyToMany as products_count');
        }

        return (int) ($category->products_count ?? 0);
    }

    /**
     * Lấy danh sách danh mục con của một danh mục
     * 
     * @param int|ProductCategory $parentId Hoặc ProductCategory object
     * @param string|null $locale
     * @return Collection
     */
    public function getChildCategories($parentId, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Nếu là ProductCategory object, lấy id
        $parent = $parentId instanceof ProductCategory ? $parentId : ProductCategory::find($parentId);

        if (!$parent) {
            return collect();
        }


        return $parent->children()
            ->active()
            ->withCount('productsManyToMany as products_count')
            ->withCount(['children as children_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->ordered()
            ->get()
            ->map(function ($category) use ($locale) {
                return $this->formatCategoryForFrontend($category, $locale);
            });
    }
}
