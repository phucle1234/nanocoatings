<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Services\PostCategoryService;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class PostApiController extends Controller
{
    protected PostService $postService;
    protected PostCategoryService $postCategoryService;

    public function __construct(PostService $postService, PostCategoryService $postCategoryService)
    {
        $this->postService = $postService;
        $this->postCategoryService = $postCategoryService;
    }

    /**
     * Lấy danh sách bài viết theo danh mục
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPostsByCategory(Request $request): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request - category_id là required
            $booleanRule = ['nullable', function ($attribute, $value, $fail) {
                if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'yes'], true)) {
                    $fail('The ' . $attribute . ' field must be a boolean.');
                }
            }];

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer|exists:postcategories,id',
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

            // Lấy category theo ID
            $category = PostCategory::with(['translations' => function ($query) use ($currentLocale) {
                $query->where('language', $currentLocale);
            }])->find($request->category_id);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Lấy category translation
            $categoryTranslation = $category->translations->firstWhere('language', $currentLocale);

            // Prepare filters
            $filters = [];
            if ($request->boolean('is_featured')) {
                $filters['is_featured'] = true;
            }
            if ($request->filled('status')) {
                $filters['status'] = $request->status;
            }

            // Get posts using service
            $sortBy = $request->get('sort_by', 'created_at');
            $perPage = $request->get('per_page', 15);

            $result = $this->postService->getPostsByCategory(
                $category,
                $filters,
                $sortBy,
                $perPage,
                $currentLocale
            );

            $posts = $result['posts'];
            $paginated = $result['pagination'];

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'slug' => $categoryTranslation->slug ?? null,
                        'name' => $categoryTranslation->name ?? 'N/A',
                        'description' => $categoryTranslation->description ?? null,
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
     * Lấy chi tiết bài viết theo slug hoặc ID
     * 
     * @param string|int $identifier Slug hoặc ID của bài viết
     * @return JsonResponse
     */
    public function show($identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Lấy bài viết theo slug hoặc ID
            $post = $this->postService->getPostBySlugOrId($identifier, $currentLocale);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài viết'
                ], 404);
            }

            // Kiểm tra trạng thái bài viết
            if (!$post->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bài viết không khả dụng'
                ], 403);
            }

            // ✅ Lấy category IDs bằng Eloquent relationship
            $postModel = Post::with('postcategories')->find($post->id);
            $categoryIds = $postModel->postcategories()
                ->where('postcategories.is_active', true)
                ->pluck('postcategories.id')
                ->toArray();
            
            // Tăng view count
            // $this->postService->incrementViewCount($post['id']);

            // Lấy bài viết liên quan (cùng category)
            $relatedPosts = $this->postService->getRelatedPosts($categoryIds, $post['id'], 6, $currentLocale);

            return response()->json([
                'success' => true,
                'data' => [
                    'post' => $post,
                    'related_posts' => $relatedPosts
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            \Log::error('Post detail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết bài viết',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
