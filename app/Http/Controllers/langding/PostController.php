<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Models\PostCategory;
use App\Services\PostService;
use App\Traits\HasImage;
use App\Traits\CarSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    use HasImage;
    use CarSearch;
    protected $postCategoryApiController;
    protected $postApiController;
    protected PostService $postService;

    public function __construct(
        PostCategoryApiController $postCategoryApiController,
        PostApiController $postApiController,
        PostService $postService
    ) {
        $this->postCategoryApiController = $postCategoryApiController;
        $this->postApiController = $postApiController;
        $this->postService = $postService;
    }

    public function show(Request $request, $slug)
    {
        try {
            // ✅ 1. Gọi API lấy chi tiết bài viết
            $response = $this->postApiController->show($slug);
            $responseData = json_decode($response->getContent(), true);

            if (!$responseData['success']) {
                return redirect()
                    ->route('post.category')
                    ->with('error', $responseData['message'] ?? 'Không tìm thấy bài viết');
            }

            $post = $responseData['data']['post'] ?? null;
            $relatedPosts = $responseData['data']['related_posts'] ?? [];

            if (!$post) {
                abort(404, 'Không tìm thấy bài viết');
            }

            // ✅ 2. Lấy danh mục cha (tin-tuc) để hiển thị breadcrumb
            $responseCategories = $this->postCategoryApiController->children($request, 'tin-tuc');
            $responseCategoriesData = json_decode($responseCategories->getContent(), true);
            $categories = $responseCategoriesData['data']['children'] ?? [];

            $carSearchData = $this->getCarSearchData();

            return view('langding.post-detail', [
                'post' => $post,
                'relatedPosts' => $relatedPosts,
                'postCategories' => $post['categories'] ?? [],
                'categories' => $categories,
                'carSearchData' => $carSearchData,
            ]);
        } catch (\Exception $e) {
            Log::error('Post detail page error: ' . $e->getMessage());

            return redirect()
                ->route('post.category')
                ->with('error', 'Có lỗi xảy ra khi tải chi tiết bài viết');
        }
    }

    /**
     * Hiển thị danh sách tin tức
     */
    public function showcategory(Request $request, $slug = null)
    {
        try {
            if(!$slug) {
                $slug = 'truyen-thong';
            }

            // ✅ Nếu không có category_id, lấy tất cả posts
            $response = $this->postCategoryApiController->children($request, $slug);
            $responseData = json_decode($response->getContent(), true);
            if (!$responseData['success']) {
                return redirect()
                    ->route('home')
                    ->with('error', 'Có lỗi xảy ra');
            }

            // Lấy danh sách categories
            $categories = $responseData['data']['children'] ?? [];

            // lấy danh sách tin nổi bật thep parent 'tin-tuc'
            $responsefeatured = $this->postCategoryApiController->featuredPosts($request, $slug);
            $responsefeaturedData = json_decode($responsefeatured->getContent(), true);
            if (!$responsefeaturedData['success']) {
                return redirect()
                    ->route('home')
                    ->with('error', 'Có lỗi xảy ra');
            }
            $ListPostFeatured = $responsefeaturedData['data']['posts'] ?? [];

            $locale = app()->getLocale();
            $categoriesWithPosts = collect($categories)->map(function ($category) use ($locale) {
                $cat = PostCategory::find($category['id'] ?? null);
                if (! $cat) {
                    return array_merge($category, [
                        'feed_posts' => [],
                        'feed_pagination' => [
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => 4,
                            'total' => 0,
                        ],
                    ]);
                }
                $result = $this->postService->getPostsByCategory($cat, [], 'sort_order', 4, $locale, 1);
                $p = $result['pagination'];

                return array_merge($category, [
                    'feed_posts' => $result['posts']->values()->all(),
                    'feed_pagination' => [
                        'current_page' => $p->currentPage(),
                        'last_page' => max(1, $p->lastPage()),
                        'per_page' => $p->perPage(),
                        'total' => $p->total(),
                    ],
                ]);
            })->toArray();


            // ✅ Nếu không có category_id, lấy tất cả posts
            $responsePostsAll = $this->postCategoryApiController->allPosts($request, $slug);
            $responseData = json_decode($responsePostsAll->getContent(), true);
            if (!$responseData['success']) {
                return redirect()
                    ->route('home')
                    ->with('error', 'Có lỗi xảy ra');
            }
            $PostAll = $responseData['data']['posts'] ?? [];
            $parent_category = $responseData['data']['parent_category'] ?? [];
            return view('langding.post-category', [
                'categories' => $categoriesWithPosts,
                'ListPostFeatured' => $ListPostFeatured,
                'PostAll' => $PostAll,
                'parent_category' => $parent_category,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('home')
                ->with('error', 'Có lỗi xảy ra khi tải danh sách tin tức');
        }
    }
}
