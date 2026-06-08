<?php

namespace App\Services;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use App\Traits\HasImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PostCategoryService
{
    use HasImage;
    /**
     * Lấy category theo slug hoặc ID
     * 
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @param string|null $locale Locale hiện tại
     * @return PostCategory|null
     */
    public function getCategoryBySlugOrId($identifier, ?string $locale = null): ?PostCategory
    {
        // Lấy locale từ app nếu không được truyền vào
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Thử tìm theo ID trước
        if (is_numeric($identifier)) {
            $category = PostCategory::with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
                ->withCount('posts as posts_count') // ✅ Fix N+1 query
                ->find($identifier);

            if ($category) {
                return $category;
            }
        }

        // Tìm theo slug trong translations — tìm ở bất kỳ ngôn ngữ nào để không bỏ sót
        // khi slug EN khác slug VI (vd: admin tạo slug khác nhau cho mỗi ngôn ngữ)
        $categoryTranslation = PostCategoryTranslation::where('slug', $identifier)
            ->where('language', $locale)
            ->first();

        // Nếu không tìm thấy theo locale hiện tại → thử tìm ở các ngôn ngữ khác
        if (!$categoryTranslation) {
            $categoryTranslation = PostCategoryTranslation::where('slug', $identifier)->first();
        }

        if ($categoryTranslation) {
            return PostCategory::with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
                ->withCount('posts as posts_count')
                ->find($categoryTranslation->postcategory_id);
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

        return PostCategory::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->withCount('posts as posts_count') // ✅ Fix N+1 query
            ->orderBy('sort_order')
            ->get();
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

        return PostCategory::query()
            ->where('is_active', true)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->withCount('posts as posts_count') // ✅ Fix N+1 query
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Format category data cho frontend (Blade views)
     * 
     * @param PostCategory $category
     * @param string $locale
     * @return object
     */
    private function formatCategoryForFrontend(PostCategory $category, string $locale): object
    {
        $translation = $category->translations->firstWhere('language', $locale);

        $category->category_name = $translation->name ?? null;
        $category->category_description = $translation->description ?? null;
        $category->category_translation_slug = $translation->slug ?? null;

        return $category;
    }

    /**
     * Lấy posts_count từ category (đảm bảo luôn là integer)
     * 
     * @param PostCategory $category
     * @return int
     */
    public function getPostsCount(PostCategory $category): int
    {
        // Nếu chưa có posts_count, load nó
        if (!isset($category->posts_count)) {
            $category->loadCount('posts as posts_count');
        }

        return (int) ($category->posts_count ?? 0);
    }

    /**
     * Lấy tất cả danh mục với filters
     * 
     * @param array $filters
     * @param bool $includeChildren
     * @param string|null $locale
     * @return Collection
     */
    public function getAllCategories(array $filters = [], bool $includeChildren = false, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $query = PostCategory::query()
            ->where('is_active', true)
            ->withCount('posts as posts_count')
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }]);

        // Filter by parent_id
        if (isset($filters['parent_id'])) {
            $parentId = $filters['parent_id'];
            if ($parentId === 'null' || $parentId === null || $parentId === '') {
                $query->whereNull('parent_id');
            } elseif (is_numeric($parentId)) {
                $query->where('parent_id', (int) $parentId);
            }
        }

        // Filter by featured
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->where('is_featured', true);
        }

        return $query->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale, $includeChildren) {
                return $this->formatCategoryForApi($category, $locale, $includeChildren);
            });
    }

    /**
     * Lấy danh mục gốc (không có parent)
     * 
     * @param string|null $locale
     * @return Collection
     */
    public function getRootCategories(?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return PostCategory::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->withCount('posts as posts_count')
            ->withCount(['children as children_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($locale) {
                return $this->formatCategoryForApi($category, $locale, false);
            });
    }

    /**
     * Lấy children của category
     * 
     * @param PostCategory $category
     * @param string|null $locale
     * @return Collection
     */
    public function getChildren(PostCategory $category, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return $category->children()
            ->where('is_active', true)
            ->withCount('posts as posts_count')
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($child) use ($locale) {
                return $this->formatCategoryForApi($child, $locale, false);
            });
    }

    /**
     * Lấy path (breadcrumb) của category
     * 
     * @param PostCategory $category
     * @param string|null $locale
     * @return array
     */
    public function getPath(PostCategory $category, ?string $locale = null): array
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $path = [];
        $current = $category;

        while ($current) {
            // Load translations if not loaded
            if (!$current->relationLoaded('translations')) {
                $current->load(['translations' => function ($q) use ($locale) {
                    $q->where('language', $locale);
                }]);
            }

            $translation = $current->translations->firstWhere('language', $locale);
            array_unshift($path, [
                'id' => $current->id,
                'slug' => $translation->slug ?? $current->slug,
                'name' => $translation->name ?? 'N/A',
            ]);

            $current = $current->parent;
        }

        return $path;
    }

    /**
     * Format category data cho API response
     * 
     * @param PostCategory $category
     * @param string $locale
     * @param bool $includeChildren
     * @return array
     */
    public function formatCategoryForApi(PostCategory $category, string $locale, bool $includeChildren = false): array
    {
        $translation = $category->translations->firstWhere('language', $locale);

        $data = [
            'id' => $category->id,
            'slug' => $translation->slug ?? null,
            'name' => $translation->name ?? 'N/A',
            'description' => $translation->description ?? null,
            'meta_title' => $translation->meta_title ?? null,
            'meta_description' => $translation->meta_description ?? null,
            'icon' => $category->icon,
            'image' => $this->getImageJson($translation->image_urls ?? []),
            'parent_id' => $category->parent_id,
            'is_featured' => $category->is_featured,
            'sort_order' => $category->sort_order,
            'posts_count' => $this->getPostsCount($category),
        ];

        // Include children if requested
        if ($includeChildren) {
            $data['children'] = $this->getChildren($category, $locale)->toArray();
        }

        // Include children_count if available
        if (isset($category->children_count)) {
            $data['children_count'] = (int) $category->children_count;
        }

        return $data;
    }

    /**
     * Format category detail cho API response (với parent, children, path)
     * 
     * @param PostCategory $category
     * @param string|null $locale
     * @return array
     */
    public function formatCategoryDetailForApi(PostCategory $category, ?string $locale = null): array
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Ensure translations and posts_count are loaded
        if (!$category->relationLoaded('translations')) {
            $category->load(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }]);
        }

        if (!isset($category->posts_count)) {
            $category->loadCount('posts as posts_count');
        }

        $translation = $category->translations->firstWhere('language', $locale);
        $parent = $category->parent;

        // Load parent translations if parent exists
        $parentData = null;
        if ($parent) {
            if (!$parent->relationLoaded('translations')) {
                $parent->load(['translations' => function ($q) use ($locale) {
                    $q->where('language', $locale);
                }]);
            }
            $parentTranslation = $parent->translations->firstWhere('language', $locale);
            $parentData = [
                'id' => $parent->id,
                'slug' => $parentTranslation->slug ?? $parent->slug,
                'name' => $parentTranslation->name ?? 'N/A',
            ];
        }

        // Get children
        $children = $this->getChildren($category, $locale);

        // Get path
        $path = $this->getPath($category, $locale);

        return [
            'id' => $category->id,
            'slug' => $translation->slug ?? null,
            'name' => $translation->name ?? 'N/A',
            'description' => $translation->description ?? null,
            'icon' => $category->icon,
            'image' => $this->getImageJson($translation->image_urls ?? []),
            'all_images' => $this->getAllImagesJson($translation->image_urls ?? []),
            'parent_id' => $category->parent_id,
            'parent' => $parentData,
            'children' => $children->toArray(),
            'path' => $path,
            'is_featured' => $category->is_featured,
            'sort_order' => $category->sort_order,
            'posts_count' => $this->getPostsCount($category),
            'meta_title' => $category->meta_title,
            'meta_description' => $category->meta_description,
            'meta_keywords' => $category->meta_keywords,
        ];
    }

    /**
     * Lấy danh sách bài viết nổi bật theo danh mục cha
     * 
     * @param object $parentCategory
     * @param int $perPage
     * @param bool $includeChildren
     * @param string|null $locale
     * @return array
     */
    public function getFeaturedPostsByParentCategory(
        $parentCategory,
        int $perPage = 12,
        bool $includeChildren = true,
        ?string $locale = null
    ): array {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Lấy tất cả category IDs (bao gồm cả parent và children)
        $categoryIds = [$parentCategory->id];

        if ($includeChildren) {
            // Lấy tất cả category con
            $childrenIds = DB::table('postcategories')
                ->where('parent_id', $parentCategory->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $categoryIds = array_merge($categoryIds, $childrenIds);
        }

        // ✅ Tối ưu: Lấy post IDs trước bằng subquery để tránh duplicate rows
        $postIds = DB::table('post_postcategory')
            ->whereIn('postcategory_id', $categoryIds)
            ->distinct()
            ->pluck('post_id')
            ->toArray();

        if (empty($postIds)) {
            return [
                'posts' => collect([]),
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1),
                'category_ids' => $categoryIds
            ];
        }

        // ✅ Query chỉ với post IDs, giảm JOIN và duplicate
        $query = DB::table('posts as p')
            ->leftJoin('post_translations as pt', function ($join) use ($locale) {
                $join->on('p.id', '=', 'pt.post_id')
                    ->where('pt.language', '=', $locale);
            })
            ->whereIn('p.id', $postIds) // ✅ Dùng post IDs thay vì join với post_postcategory
            ->where('p.is_active', true)
            ->where('p.is_featured', true) // ✅ Chỉ lấy bài viết nổi bật
            ->select(
                'p.id',
                'p.postcategory_id',
                'p.icon',
                'p.status',
                'p.is_featured',
                'p.is_active',
                'p.sort_order',
                'p.view_count',
                'p.published_at',
                'p.meta_title',
                'p.meta_description',
                'p.meta_keywords',
                'p.created_at',
                'p.updated_at',
                'pt.title',
                'pt.slug',
                'pt.meta_description as translation_meta_description',
                'pt.image_urls'
            )
            ->orderBy('p.sort_order', 'asc')
            ->orderBy('p.published_at', 'desc');
        // ✅ Bỏ distinct() vì không còn duplicate

        // Paginate
        $paginated = $query->paginate($perPage);

        // ✅ Tối ưu: Batch load category info cho tất cả posts cùng lúc
        $postIdsForCategory = $paginated->getCollection()->pluck('id')->toArray();
        $categoryInfoMap = [];

        if (!empty($postIdsForCategory)) {
            $categoryInfos = DB::table('post_postcategory as ppc')
                ->leftJoin('postcategories as pc', 'ppc.postcategory_id', '=', 'pc.id')
                ->leftJoin('postcategory_translations as pct', function ($join) use ($locale) {
                    $join->on('pc.id', '=', 'pct.postcategory_id')
                        ->where('pct.language', '=', $locale);
                })
                ->whereIn('ppc.post_id', $postIdsForCategory)
                ->where('ppc.is_primary', true) // ✅ Lấy category chính
                ->select('ppc.post_id', 'pc.id as category_id', 'pct.name as category_name')
                ->get();

            // Tạo map để lookup nhanh
            foreach ($categoryInfos as $info) {
                $categoryInfoMap[$info->post_id] = [
                    'category_id' => $info->category_id,
                    'category_name' => $info->category_name
                ];
            }
        }

        // Transform posts và lấy category info từ map
        $posts = $paginated->getCollection()->map(function ($post) use ($locale, $categoryInfoMap) {
            // ✅ Lấy category info từ map (đã batch load)
            $categoryInfo = $categoryInfoMap[$post->id] ?? null;
            $post->category_id = $categoryInfo['category_id'] ?? null;
            $post->category_name = $categoryInfo['category_name'] ?? null;

            // Xử lý image - trả về string URL
            if (!empty($post->image_urls)) {
                $post->image = $this->getImageJson($post->image_urls);
                $imageUrlsArray = is_string($post->image_urls)
                    ? json_decode($post->image_urls, true)
                    : $post->image_urls;
                $post->all_images = $this->getAllImagesJson($imageUrlsArray);
            } else {
                $post->image = null;
                $post->all_images = [];
            }

            return $post;
        });

        return [
            'posts' => $posts,
            'pagination' => $paginated,
            'category_ids' => $categoryIds
        ];
    }


    /**
     * Lấy TẤT CẢ bài viết theo danh mục cha (bao gồm cả danh mục con)
     * 
     * @param object $parentCategory
     * @param int $perPage
     * @param bool $includeChildren
     * @param string $sortBy
     * @param string $order
     * @param array $filters
     * @param string|null $locale
     * @return array
     */
    public function getAllPostsByParentCategory(
        $parentCategory,
        int $perPage = 12,
        bool $includeChildren = true,
        string $sortBy = 'created_at',
        string $order = 'desc',
        array $filters = [],
        ?string $locale = null
    ): array {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Lấy tất cả category IDs (bao gồm cả parent và children)
        $categoryIds = [$parentCategory->id];

        if ($includeChildren) {
            // Lấy tất cả category con
            $childrenIds = DB::table('postcategories')
                ->where('parent_id', $parentCategory->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $categoryIds = array_merge($categoryIds, $childrenIds);
        }

        // ✅ Tối ưu: Lấy post IDs trước bằng subquery để tránh duplicate rows
        $postIds = DB::table('post_postcategory')
            ->whereIn('postcategory_id', $categoryIds)
            ->distinct()
            ->pluck('post_id')
            ->toArray();

        if (empty($postIds)) {
            return [
                'posts' => collect([]),
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1),
                'category_ids' => $categoryIds
            ];
        }

        // ✅ Query chỉ với post IDs, giảm JOIN và duplicate
        $query = DB::table('posts as p')
            ->leftJoin('post_translations as pt', function ($join) use ($locale) {
                $join->on('p.id', '=', 'pt.post_id')
                    ->where('pt.language', '=', $locale);
            })
            ->whereIn('p.id', $postIds) // ✅ Dùng post IDs thay vì join với post_postcategory
            ->where('p.is_active', true)
            ->select(
                'p.id',
                'p.postcategory_id',
                'p.icon',
                'p.status',
                'p.is_featured',
                'p.is_active',
                'p.sort_order',
                'p.view_count',
                'p.published_at',
                'p.meta_title',
                'p.meta_description',
                'p.meta_keywords',
                'p.created_at',
                'p.updated_at',
                'pt.title',
                'pt.slug',
                'pt.meta_description as translation_meta_description',
                'pt.image_urls'
            );
        // ✅ Bỏ distinct() vì không còn duplicate

        // ✅ Apply filters
        if (isset($filters['is_featured'])) {
            $query->where('p.is_featured', $filters['is_featured']);
        }

        // ✅ Apply sorting
        switch ($sortBy) {
            case 'sort_order':
                $query->orderBy('p.sort_order', $order);
                break;
            case 'published_at':
                $query->orderBy('p.published_at', $order);
                break;
            case 'view_count':
                $query->orderBy('p.view_count', $order);
                break;
            case 'title':
                $query->orderBy('pt.title', $order);
                break;
            default:
                $query->orderBy('p.created_at', $order);
        }

        // Thêm sort mặc định để đảm bảo kết quả nhất quán
        if ($sortBy !== 'created_at') {
            $query->orderBy('p.created_at', 'desc');
        }

        // Paginate
        $paginated = $query->paginate($perPage);

        // ✅ Tối ưu: Batch load category info cho tất cả posts cùng lúc
        $postIdsForCategory = $paginated->getCollection()->pluck('id')->toArray();
        $categoryInfoMap = [];

        if (!empty($postIdsForCategory)) {
            $categoryInfos = DB::table('post_postcategory as ppc')
                ->leftJoin('postcategories as pc', 'ppc.postcategory_id', '=', 'pc.id')
                ->leftJoin('postcategory_translations as pct', function ($join) use ($locale) {
                    $join->on('pc.id', '=', 'pct.postcategory_id')
                        ->where('pct.language', '=', $locale);
                })
                ->whereIn('ppc.post_id', $postIdsForCategory)
                ->where('ppc.is_primary', true) // ✅ Lấy category chính
                ->select('ppc.post_id', 'pc.id as category_id', 'pct.name as category_name')
                ->get();

            // Tạo map để lookup nhanh
            foreach ($categoryInfos as $info) {
                $categoryInfoMap[$info->post_id] = [
                    'category_id' => $info->category_id,
                    'category_name' => $info->category_name
                ];
            }
        }

        // Transform posts và lấy category info từ map
        $posts = $paginated->getCollection()->map(function ($post) use ($locale, $categoryInfoMap) {
            // ✅ Lấy category info từ map (đã batch load)
            $categoryInfo = $categoryInfoMap[$post->id] ?? null;
            $post->category_id = $categoryInfo['category_id'] ?? null;
            $post->category_name = $categoryInfo['category_name'] ?? null;

            // Xử lý image - trả về string URL
            if (!empty($post->image_urls)) {
                $post->image = $this->getImageJson($post->image_urls);
                $imageUrlsArray = is_string($post->image_urls)
                    ? json_decode($post->image_urls, true)
                    : $post->image_urls;
                $post->all_images = $this->getAllImagesJson($imageUrlsArray);
            } else {
                $post->image = null;
                $post->all_images = [];
            }

            // Format thêm thông tin
            $post->published_at = $post->published_at
                ? \Carbon\Carbon::parse($post->published_at)->format('Y-m-d H:i:s')
                : null;
            $post->created_at = $post->created_at
                ? \Carbon\Carbon::parse($post->created_at)->format('Y-m-d H:i:s')
                : null;

            return $post;
        });

        return [
            'posts' => $posts,
            'pagination' => $paginated,
            'category_ids' => $categoryIds
        ];
    }
}
