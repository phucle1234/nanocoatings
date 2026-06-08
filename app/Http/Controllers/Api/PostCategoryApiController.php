<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Services\PostCategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PostCategoryApiController extends Controller
{

    protected PostService $postService;
    protected PostCategoryService $postCategoryService;

    public function __construct(PostService $postService, PostCategoryService $postCategoryService)
    {
        $this->postService = $postService;
        $this->postCategoryService = $postCategoryService;
    }

    /**
     * Lấy danh sách tất cả danh mục tin tức
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request
            $validator = Validator::make($request->all(), [
                'parent_id' => 'nullable',
                'is_featured' => ['nullable', function ($attribute, $value, $fail) {
                    if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                        $fail('The ' . $attribute . ' field must be a boolean.');
                    }
                }],
                'include_children' => ['nullable', function ($attribute, $value, $fail) {
                    if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                        $fail('The ' . $attribute . ' field must be a boolean.');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare filters
            $filters = [];
            if ($request->has('parent_id')) {
                $filters['parent_id'] = $request->parent_id;
            }
            if ($request->boolean('is_featured')) {
                $filters['is_featured'] = true;
            }

            // Get categories using service
            $includeChildren = $request->boolean('include_children');
            $categories = $this->postCategoryService->getAllCategories($filters, $includeChildren, $currentLocale);

            return response()->json([
                'success' => true,
                'data' => $categories->values(),
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách danh mục',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một danh mục tin tức
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @return JsonResponse
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            $category = $this->postCategoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Format category detail using service
            $data = $this->postCategoryService->formatCategoryDetailForApi($category, $currentLocale);

            return response()->json([
                'success' => true,
                'data' => $data,
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết danh mục',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách bài viết trong danh mục
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @return JsonResponse
     */
    public function posts(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request
            $booleanRule = ['nullable', function ($attribute, $value, $fail) {
                if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                    $fail('The ' . $attribute . ' field must be a boolean.');
                }
            }];

            $validator = Validator::make($request->all(), [
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'is_featured' => $booleanRule,
                'status' => 'nullable|string|in:draft,published,archived',
                'sort_by' => 'nullable|string|in:sort_order,created_at,published_at,view_count,title',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = $this->postCategoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Format category info using service
            $categoryData = $this->postCategoryService->formatCategoryForApi($category, $currentLocale, false);

            // Prepare filters
            $filters = [];
            if ($request->boolean('is_featured')) {
                $filters['is_featured'] = true;
            }
            if ($request->has('status')) {
                $filters['status'] = $request->status;
            }

            // Get posts using service
            $sortBy = $request->get('sort_by', 'sort_order');
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $result = $this->postService->getPostsByCategory(
                $category,
                $filters,
                $sortBy,
                $perPage,
                $currentLocale,
                (int)$page
            );

            $posts = $result['posts'];
            $paginated = $result['pagination'];

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => [
                        'id' => $categoryData['id'],
                        'slug' => $categoryData['slug'],
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'],
                        'image' => $categoryData['image'],
                        'icon' => $categoryData['icon'] ?? null,
                        'meta_title' => $categoryData['meta_title'] ?? null,
                        'meta_description' => $categoryData['meta_description'] ?? null,
                    ],
                    'posts' => $posts,
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                        'from' => $paginated->firstItem(),
                        'to' => $paginated->lastItem(),
                    ]
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách bài viết',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách danh mục nổi bật
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Get featured categories using service
            $categories = $this->postCategoryService->getFeaturedCategories($currentLocale)
                ->map(function ($category) use ($currentLocale) {
                    return $this->postCategoryService->formatCategoryForApi($category, $currentLocale, false);
                });

            return response()->json([
                'success' => true,
                'data' => $categories->values(),
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách danh mục nổi bật',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách danh mục gốc (không có parent)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function root(Request $request): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Get root categories using service
            $categories = $this->postCategoryService->getRootCategories($currentLocale)
                ->map(function ($category) use ($currentLocale) {
                    return $this->postCategoryService->formatCategoryForApi($category, $currentLocale, false);
                });

            return response()->json([
                'success' => true,
                'data' => $categories->values(),
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách danh mục gốc',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách danh mục con
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục cha
     * @return JsonResponse
     */
    public function children(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            $category = $this->postCategoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Get children using service
            $children = $this->postCategoryService->getChildren($category, $currentLocale);

            // Get parent info
            if (!$category->relationLoaded('translations')) {
                $category->load(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }]);
            }
            $translation = $category->translations->firstWhere('language', $currentLocale);

            return response()->json([
                'success' => true,
                'data' => [
                    'parent' => [
                        'id' => $category->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                    ],
                    'children' => $children->values()
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách danh mục con',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách bài viết nổi bật theo danh mục cha
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục cha
     * @return JsonResponse
     */
    public function featuredPosts(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'include_children' => ['nullable', function ($attribute, $value, $fail) {
                    if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                        $fail('The ' . $attribute . ' field must be a boolean.');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Lấy thông tin danh mục cha
            $parentCategory = $this->postCategoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$parentCategory || !$parentCategory->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục cha'
                ], 404);
            }

            $perPage = $request->get('per_page', 12);
            $includeChildren = $request->boolean('include_children', true);

            // Lấy danh sách bài viết nổi bật
            $result = $this->postCategoryService->getFeaturedPostsByParentCategory(
                $parentCategory,
                $perPage,
                $includeChildren,
                $currentLocale
            );

            $posts = $result['posts'];
            $paginated = $result['pagination'];
            $categoryIds = $result['category_ids'];

            // Format parent category info
            if (!$parentCategory->relationLoaded('translations')) {
                $parentCategory->load(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }]);
            }
            $translation = $parentCategory->translations->firstWhere('language', $currentLocale);

            return response()->json([
                'success' => true,
                'data' => [
                    'parent_category' => [
                        'id' => $parentCategory->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                    ],
                    'category_ids' => $categoryIds,
                    'posts' => $posts,
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                        'from' => $paginated->firstItem(),
                        'to' => $paginated->lastItem(),
                    ]
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            \Log::error('Featured posts by parent category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách bài viết nổi bật',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy danh sách tất cả bài viết theo danh mục cha
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục cha
     * @return JsonResponse
     */
    public function allPosts(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'include_children' => ['nullable', function ($attribute, $value, $fail) {
                    if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                        $fail('The ' . $attribute . ' field must be a boolean.');
                    }
                }],
                'is_featured' => ['nullable', function ($attribute, $value, $fail) {
                    if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                        $fail('The ' . $attribute . ' field must be a boolean.');
                    }
                }],
                'sort_by' => 'nullable|string|in:sort_order,created_at,published_at,view_count,title',
                'order' => 'nullable|string|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Lấy thông tin danh mục cha
            $parentCategory = $this->postCategoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$parentCategory || !$parentCategory->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục cha'
                ], 404);
            }

            $perPage = $request->get('per_page', 12);
            $includeChildren = $request->boolean('include_children', true);
            $sortBy = $request->get('sort_by', 'created_at');
            $order = $request->get('order', 'desc');

            // Prepare filters
            $filters = [];
            if ($request->has('is_featured')) {
                $filters['is_featured'] = $request->boolean('is_featured');
            }

            // Lấy danh sách TẤT CẢ bài viết theo danh mục cha
            $result = $this->postCategoryService->getAllPostsByParentCategory(
                $parentCategory,
                $perPage,
                $includeChildren,
                $sortBy,
                $order,
                $filters,
                $currentLocale
            );

            $posts = $result['posts'];
            $paginated = $result['pagination'];
            $categoryIds = $result['category_ids'];

            // Format parent category info
            if (!$parentCategory->relationLoaded('translations')) {
                $parentCategory->load(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }]);
            }
            $translation = $parentCategory->translations->firstWhere('language', $currentLocale);

            return response()->json([
                'success' => true,
                'data' => [
                    'parent_category' => [
                        'id' => $parentCategory->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                    ],
                    'category_ids' => $categoryIds,
                    'posts' => $posts,
                    'filters' => [
                        'is_featured' => $request->has('is_featured') ? $request->boolean('is_featured') : null,
                        'sort_by' => $sortBy,
                        'order' => $order,
                        'include_children' => $includeChildren,
                    ],
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                        'from' => $paginated->firstItem(),
                        'to' => $paginated->lastItem(),
                    ]
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            \Log::error('All posts by parent category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách bài viết',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
