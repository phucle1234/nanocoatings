<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategory;
use App\Traits\HasImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class PostService
{
    use HasImage;

    /**
     * Lấy danh sách bài viết theo danh mục
     * 
     * @param PostCategory $category
     * @param array $filters
     * @param string $sortBy
     * @param int $perPage
     * @param string|null $locale
     * @param int|null $page
     * @return array ['posts' => Collection, 'pagination' => LengthAwarePaginator]
     */
    public function getPostsByCategory(
        PostCategory $category,
        array $filters = [],
        string $sortBy = 'sort_order',
        int $perPage = 15,
        ?string $locale = null,
        ?int $page = null
    ): array {
        // Lấy locale từ app nếu không được truyền vào
        if ($locale === null) {
            $locale = app()->getLocale();
        }
        $currentPage = $page ?? 1;
        // Query posts
        $query = Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->whereHas('postcategories', function ($q) use ($category) {
                $q->where('postcategories.id', $category->id);
            });

        // Apply filters
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->where('is_featured', true);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['published_after'])) {
            $query->where('published_at', '>=', $filters['published_after']);
        }

        if (isset($filters['published_before'])) {
            $query->where('published_at', '<=', $filters['published_before']);
        }

        // Apply sorting
        $this->applySorting($query, $sortBy, $locale);

        // Paginator::currentPageResolver(function () use ($currentPage) {
        //     return $currentPage;
        // });

        // Pagination
        // $paginated = $query->paginate($perPage, ['*'], 'page', $currentPage);
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        $paginated = $query->paginate($perPage);

        // Format posts
        $posts = $paginated->getCollection()->map(function ($post) use ($locale) {
            return $this->formatPost($post, $locale);
        });

        return [
            'posts' => $posts,
            'pagination' => $paginated
        ];
    }

    /**
     * Áp dụng sorting cho query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param string $locale
     * @return void
     */
    private function applySorting($query, string $sortBy, string $locale): void
    {
        switch ($sortBy) {
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'published_at':
                $query->orderBy('published_at', 'desc');
                break;
            case 'view_count':
                $query->orderBy('view_count', 'desc');
                break;
            case 'sort_order':
                $query->orderBy('sort_order', 'asc');
                break;
            case 'title':
                // Sort by translation title using subquery to avoid join conflicts
                $query->orderByRaw('(
                    SELECT title 
                    FROM post_translations 
                    WHERE post_translations.post_id = posts.id 
                    AND post_translations.language = ?
                    LIMIT 1
                ) ASC', [$locale]);
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Lấy tất cả bài viết
     * 
     * @param int $limit
     * @param string|null $locale
     * @return Collection
     */
    public function getAllPosts(int $limit = 15, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($post) use ($locale) {
                return $this->formatPostForFrontend($post, $locale);
            });
    }

    /**
     * Lấy bài viết theo danh mục ID
     * 
     * @param int $categoryId
     * @param string|null $locale
     * @return Collection
     */
    public function getPostsByCategoryId(int $categoryId, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->whereHas('postcategories', function ($q) use ($categoryId) {
                $q->where('postcategories.id', $categoryId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($locale) {
                return $this->formatPostForFrontend($post, $locale);
            });
    }

    /**
     * Format post data cho API response
     * 
     * @param Post $post
     * @param string $locale
     * @return array
     */
    private function formatPost(Post $post, string $locale): array
    {
        $translation = $post->translations->firstWhere('language', $locale);

        return [
            'id' => $post->id,
            'title' => $translation->title ?? 'N/A',
            'slug' => $translation->slug ?? null,
            'content' => $translation->content ?? null,
            'excerpt' => $translation->excerpt ?? null,
            'status' => $post->status,
            'post_type' => $post->post_type,
            'document_file_id' => $post->document_file_id,
            'section_type' => $post->section_type,
            'is_featured' => $post->is_featured,
            'is_active' => $post->is_active,
            'view_count' => $post->view_count,
            'url' => $translation->url ?? null,
            'image' => $this->getImageJson($translation->image_urls ?? []),
            'all_images' => $this->getAllImagesJson($translation->image_urls ?? []),
            'published_at' => $post->published_at?->toISOString(),
            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
        ];
    }

    /**
     * Lấy bài viết theo slug hoặc ID (dùng cho frontend)
     * 
     * @param string|int $identifier Slug hoặc ID
     * @param string|null $locale
     * @return Post|null
     */
    public function getPostBySlugOrId($identifier, ?string $locale = null): ?Post
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $query = Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }]);

        if (is_numeric($identifier)) {
            $post = $query->find($identifier);
        } else {
            $post = $query->whereHas('translations', function ($q) use ($identifier, $locale) {
                $q->where('slug', $identifier)->where('language', $locale);
            })->first();
        }

        if ($post) {
            return $this->formatPostForFrontend($post, $locale);
        }

        return null;
    }

    /**
     * Lấy bài viết liên quan theo category
     * 
     * @param int $categoryId
     * @param int $excludePostId
     * @param int $limit
     * @param string|null $locale
     * @return Collection
     */
    public function getRelatedPosts($categoryIds, int $excludePostId, int $limit = 6, ?string $locale = null): array
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // ✅ Chuyển đổi sang array nếu là single ID
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        // ✅ Kiểm tra empty array
        if (empty($categoryIds)) {
            return [];
        }

        // Query bài viết liên quan
        $relatedPosts = Post::query()
            ->where('is_active', true)
            ->where('id', '!=', $excludePostId)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->whereHas('postcategories', function ($q) use ($categoryIds) {
                $q->whereIn('postcategories.id', $categoryIds)
                    ->where('postcategories.is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // ✅ Format posts và return array
        return $relatedPosts->map(function ($post) use ($locale) {
            $translation = $post->translations->firstWhere('language', $locale);

            $imageUrls = $translation->image_urls ?? [];
            $image = $this->getImageJson($imageUrls);
            $allImages = $this->getAllImagesJson($imageUrls);

            return [
                'id' => $post->id,
                'slug' => $translation->slug ?? null,
                'title' => $translation->title ?? 'Untitled',
                'excerpt' => $translation->meta_description ?? null,
                'image' => $image,
                'all_images' => $allImages,
                'is_featured' => (bool) $post->is_featured,
                'view_count' => (int) $post->view_count,
                'published_at' => $post->published_at
                    ? \Carbon\Carbon::parse($post->published_at)->format('Y-m-d H:i:s')
                    : null,
                'created_at' => $post->created_at
                    ? \Carbon\Carbon::parse($post->created_at)->format('Y-m-d H:i:s')
                    : null,
            ];
        })->toArray(); // ✅ Convert to array
    }

    /**
     * Format post data cho frontend (Blade views)
     * 
     * @param Post $post
     * @param string|null $locale
     * @return object
     */
    public function formatPostForFrontend(Post $post, ?string $locale = null): object
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $translation = $post->translations->firstWhere('language', $locale);

        $post->title = $translation?->title ?? null;
        $post->content = $translation?->content ?? null;
        $post->excerpt = $translation?->excerpt ?? null;
        $post->slug = $translation?->slug ?? null;
        $imageUrls = $translation->image_urls ?? [];
        $post->image = $this->getImageJson($imageUrls);
        $post->all_images = $this->getAllImagesJson($imageUrls);

        return $post;
    }

    /**
     * Gom tất cả URL ảnh từ các bài (mảng giống formatPost API) thành danh sách ô gallery.
     *
     * @param  array<int, array<string, mixed>>  $posts
     * @return array<int, array{image_url: string, title: string, content: string, show_caption: bool}>
     */
    public function flattenAwardPostsToImageTiles(array $posts): array
    {
        $tiles = [];

        foreach ($posts as $award) {
            if (! is_array($award)) {
                $award = (array) $award;
            }

            $allImages = $award['all_images'] ?? null;
            if (! empty($allImages) && is_array($allImages)) {
                $imageUrls = array_values(array_filter($allImages));
            } else {
                $imageUrls = [];
            }

            if (empty($imageUrls)) {
                if (! empty($award['image']['url'])) {
                    $imageUrls = [asset($award['image']['url'])];
                } elseif (! empty($award['image'])) {
                    $img = $award['image'];
                    $imageUrls = [is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))
                        ? $img
                        : asset($img)];
                } else {
                    $imageUrls = [asset('langding/imgs/new.png')];
                }
            }

            $title = $award['title'] ?? $award['meta_title'] ?? '';
            $content = $award['content'] ?? $award['excerpt'] ?? $award['description'] ?? '';

            foreach ($imageUrls as $imgIndex => $imageUrl) {
                $tiles[] = [
                    'image_url' => $imageUrl,
                    'title' => $title,
                    'content' => $content,
                    'show_caption' => $imgIndex === 0 && $content !== '' && $content !== null,
                ];
            }
        }

        return $tiles;
    }

    /**
     * Phân trang gallery giải thưởng theo số ô ảnh (một trang = N ảnh), không theo số bài.
     *
     * @return array{tiles: array<int, array<string, mixed>>, pagination: array<string, int|float|null>}
     */
    public function getAwardGalleryImagePage(int $categoryId, int $page = 1, int $imagesPerPage = 12, ?string $locale = null): array
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $imagesPerPage = max(1, $imagesPerPage);

        $query = Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->whereHas('postcategories', function ($q) use ($categoryId) {
                $q->where('postcategories.id', $categoryId);
            });

        $this->applySorting($query, 'sort_order', $locale);

        $posts = $query->get();
        $postArrays = $posts->map(function ($post) use ($locale) {
            return $this->formatPost($post, $locale);
        })->values()->all();

        $tiles = $this->flattenAwardPostsToImageTiles($postArrays);
        $total = count($tiles);
        $lastPage = max(1, (int) ceil($total / $imagesPerPage));
        $page = min(max(1, $page), $lastPage);
        $offset = ($page - 1) * $imagesPerPage;
        $pageTiles = array_slice($tiles, $offset, $imagesPerPage);

        return [
            'tiles' => $pageTiles,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $imagesPerPage,
                'total' => $total,
                'from' => $total > 0 ? $offset + 1 : null,
                'to' => $total > 0 ? min($offset + count($pageTiles), $total) : null,
            ],
        ];
    }

    /**
     * Lấy toàn bộ tiles gallery giải thưởng (dùng cho Fancybox xem next/prev trên toàn danh mục).
     *
     * @return array<int, array{image_url: string, title: string, content: string, show_caption: bool}>
     */
    public function getAwardGalleryAllTiles(int $categoryId, ?string $locale = null): array
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $query = Post::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->whereHas('postcategories', function ($q) use ($categoryId) {
                $q->where('postcategories.id', $categoryId);
            });

        $this->applySorting($query, 'sort_order', $locale);

        $posts = $query->get();
        $postArrays = $posts->map(function ($post) use ($locale) {
            return $this->formatPost($post, $locale);
        })->values()->all();

        return $this->flattenAwardPostsToImageTiles($postArrays);
    }
}
