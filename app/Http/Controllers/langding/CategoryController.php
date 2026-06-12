<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Traits\CarSearch;
use App\Traits\HasImage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use CarSearch;
    use HasImage;

    protected CategoryApiController $categoryApiController;
    protected PostCategoryApiController $postCategoryApiController;
    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(
        CategoryApiController $categoryApiController,
        PostCategoryApiController $postCategoryApiController,
        ProductService $productService,
        CategoryService $categoryService
    ) {
        $this->categoryApiController = $categoryApiController;
        $this->postCategoryApiController = $postCategoryApiController;
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request, $slug = null)
    {
        try {
            // ✅ Nếu có slug, get category theo slug
            if ($slug) {
                $categoryResponse = $this->categoryApiController->show($request, $slug);
                $categoryData = json_decode($categoryResponse->getContent(), true);

                if (!$categoryData['success']) {
                    // Nếu không tìm thấy, lấy category đầu tiên
                    $slug = null;
                } else {
                    $selectedRootCategory = $categoryData['data'];
                }
            }

            // ✅ Nếu không có slug hoặc không tìm thấy, lấy danh mục đầu tiên
            if (!$slug) {
                $rootCategoriesResponse = $this->categoryApiController->root($request);
                $rootCategoriesData = json_decode($rootCategoriesResponse->getContent(), true);

                if ($rootCategoriesData['success'] && !empty($rootCategoriesData['data'])) {
                    $selectedRootCategory = $rootCategoriesData['data'][0];
                    $slug = $selectedRootCategory['slug'];
                } else {
                    // Không có category nào
                    return view('langding.category', [
                        'selectedRootCategory' => null,
                        'childCategories' => [],
                        'allProducts' => [],
                        'bestsellerProducts' => collect(),
                    ]);
                }
            }

            // ✅ Lấy danh sách category con
            $childrenResponse = $this->categoryApiController->children($request, $slug);
            $childrenData = json_decode($childrenResponse->getContent(), true);

            $childCategories = [];
            if ($childrenData['success']) {
                $childCategories = $childrenData['data']['children'] ?? [];
            }

            // ✅ Kiểm tra xem category hiện tại có phải là danh mục cha không
            $isParentCategory = empty($selectedRootCategory['parent']) ||
                $selectedRootCategory['parent'] === null ||
                $selectedRootCategory['parent_id'] === null;

            // ✅ Lấy products nổi bật từ danh mục cha
            $allProducts = [];
            $pagination = null;
            $page = $request->get('page', 1);
            $perPage = 12; // Số sản phẩm mỗi trang

            $productsRequest = new Request([
                'page' => $page,
                'per_page' => $perPage
            ]);

            $productsResponse = $this->categoryApiController->products($productsRequest, $slug);
            $productsData = json_decode($productsResponse->getContent(), true);

            if ($productsData['success']) {
                $allProducts = $productsData['data']['products'] ?? [];
                $pagination = $productsData['data']['pagination'] ?? null;

                // ✅ Lấy tên category từ API response
                $categoryName = $productsData['data']['category']['name'] ?? null;

                // ✅ Thêm category_name vào từng product
                foreach ($allProducts as &$product) {
                    $product['category_name'] = $categoryName;
                }
            }

            // ✅ Lấy products cho từng danh mục con (giới hạn 6 sản phẩm)
            foreach ($childCategories as &$category) {
                $childSlug = $category['slug'] ?? $category['id'];
                $childRequest = new Request(['per_page' => 6]);

                $childProductsResponse = $this->categoryApiController->products($childRequest, $childSlug);
                $childProductsData = json_decode($childProductsResponse->getContent(), true);

                if ($childProductsData['success']) {
                    $category['products'] = $childProductsData['data']['products'] ?? [];
                } else {
                    $category['products'] = [];
                }
            }

            // ✅ Nếu là danh mục con, lấy các sibling categories (anh em cùng parent)
            $siblingCategories = [];
            if (!$isParentCategory && !empty($selectedRootCategory['parent_id'])) {
                $siblingResponse = $this->categoryApiController->children($request, $selectedRootCategory['parent_id']);
                $siblingData = json_decode($siblingResponse->getContent(), true);

                if ($siblingData['success']) {
                    $allSiblings = $siblingData['data']['children'] ?? [];
                    $siblingCategories = $allSiblings;
                }
            }
            // ✅ Lấy bài viết từ danh mục banner "danh-muc-san-pham-venturer"

            $allPosts = [];
            $firstInfoPost = null;
            $twoBannerPost = null;
            $otherBannerPosts = [];
            $designPosts = [];
            $technologyPosts = [];
            $experiencePosts = [];
            $bannerPostsData = null;

            try {
                $bannerRequest = new Request([
                    'per_page' => 50,
                    'sort_by' => 'sort_order',
                    'page' => 1,
                    'current_page' => 1
                ]);

                // Luôn dùng VI slug để tra banner post category, tránh mismatch khi EN slug khác VI slug
                // (post category banner thường chỉ được tạo với slug tiếng Việt)
                $bannerSlug = \App\Models\ProductCategoryTranslation::where('category_id', $selectedRootCategory['id'] ?? null)
                    ->where('language', app()->getLocale())
                    ->value('slug') ?? $slug;
  
                $postCategoryApiController = app(\App\Http\Controllers\Api\PostCategoryApiController::class);
                $bannerPostsResponse = $postCategoryApiController->posts($bannerRequest, $bannerSlug);
                $bannerPostsData = json_decode($bannerPostsResponse->getContent(), true);

                if (data_get($bannerPostsData, 'success')) {
                    $allPosts = data_get($bannerPostsData, 'data.posts', []);
                }
            } catch (\Exception $e) {
                $bannerPostsData = null; // ✅ Đảm bảo có giá trị mặc định
            }


            $firstInfoPost = !empty($allPosts[0]) ? $allPosts[0] : null;
            $twoBannerPost = !empty($allPosts[1]) ? $allPosts[1] : null;
            $otherBannerPosts = !empty($allPosts) ? array_slice($allPosts, 2, 5) : [];
            // dd($selectedRootCategory);
            $bestsellerProducts = $this->productService->getBestsellerProducts(10, app()->getLocale());

            $currentLocale = app()->getLocale();
            $relatedCategories = $this->categoryService->getRootCategories($currentLocale)
                ->filter(fn ($cat) => $cat->id != ($selectedRootCategory['id'] ?? null));

            foreach ($relatedCategories as $category) {
                $category->category_image = $this->getImageJson($category->category_image_urls);
            }

            $viewData = [
                'siblingCategories' => $siblingCategories,
                'relatedCategories' => $relatedCategories,
                'selectedRootCategory' => $selectedRootCategory,
                'childCategories' => $childCategories,
                'allProducts' => $allProducts,
                'bestsellerProducts' => $bestsellerProducts,
                'pagination' => $pagination,
                'firstBannerPost' => $firstInfoPost,
                'twoBannerPost' => $twoBannerPost,
                'otherBannerPosts' => $otherBannerPosts,

                'designPosts' => $designPosts,
                'technologyPosts' => $technologyPosts,
                'experiencePosts' => $experiencePosts,



                'categorySettings' => [
                    'autoplay_speed' => data_get($bannerPostsData, 'data.category.icon', 3000),
                ],
            ];

            // Kiểm tra nếu danh mục có danh mục con
            $hasChildren = !empty($childCategories) && count($childCategories) > 0;

            if ($hasChildren) {
                return view('langding.category', $viewData);
            } else {
                return view('langding.category-sub', $viewData);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            return view('langding.category', [
                'selectedRootCategory' => null,
                'childCategories' => [],
                'allProducts' => [],
                'bestsellerProducts' => collect(),
            ])->withErrors(['error' => 'Không thể tải dữ liệu. Vui lòng thử lại sau.']);
        }
    }

    /**
     * Hiển thị sản phẩm mới (is_new = 1) của danh mục
     */
    public function news(Request $request, $slug)
    {
        try {

            // ✅ Lấy category theo slug
            $categoryResponse = $this->categoryApiController->show($request, $slug);
            $categoryData = json_decode($categoryResponse->getContent(), true);

            if (!$categoryData['success']) {
                // Nếu không tìm thấy category, redirect về trang chủ hoặc 404
                abort(404, 'Danh mục không tồn tại');
            }

            $selectedRootCategory = $categoryData['data'];

            // ✅ Lấy danh sách category con
            $childrenResponse = $this->categoryApiController->children($request, $slug);
            $childrenData = json_decode($childrenResponse->getContent(), true);

            $childCategories = [];
            if ($childrenData['success']) {
                $childCategories = $childrenData['data']['children'] ?? [];
            }

            // ✅ Kiểm tra xem category hiện tại có phải là danh mục cha không
            $isParentCategory = empty($selectedRootCategory['parent']) ||
                $selectedRootCategory['parent'] === null ||
                $selectedRootCategory['parent_id'] === null;

            // ✅ Lấy sản phẩm mới (is_new = 1) và sắp xếp theo created_at DESC
            $allProducts = [];
            $productsRequest = new Request([
                'is_new' => true,
                'sort_by' => 'created_at',
                'per_page' => 12 // Lấy nhiều sản phẩm mới hơn
            ]);
            $productsResponse = $this->categoryApiController->products($productsRequest, $slug);
            $productsData = json_decode($productsResponse->getContent(), true);

            if ($productsData['success']) {
                $allProducts = $productsData['data']['products'] ?? [];

                // ✅ Lấy tên category từ API response
                $categoryName = $productsData['data']['category']['name'] ?? null;

                // ✅ Thêm category_name vào từng product
                foreach ($allProducts as &$product) {
                    $product['category_name'] = $categoryName;
                }
            }

            // ✅ Lấy bài viết từ danh mục banner

            try {
                $bannerRequest = new Request([
                    'per_page' => 50,
                    'sort_by' => 'sort_order',
                    'page' => 1,
                    'current_page' => 1
                ]);
                $bannerSlug = \App\Models\ProductCategoryTranslation::where('category_id', $selectedRootCategory['id'] ?? null)
                    ->where('language', 'vi')
                    ->value('slug') ?? $slug;
                $postCategoryApiController = app(\App\Http\Controllers\Api\PostCategoryApiController::class);
                $bannerPostsResponse = $postCategoryApiController->posts($bannerRequest, $bannerSlug);
                $bannerPostsData = json_decode($bannerPostsResponse->getContent(), true);
            } catch (\Exception $e) {
                $bannerPostsData = null;
                \Log::error('Error loading banner posts: ' . $e->getMessage());
            }

            $bannerPosts = data_get($bannerPostsData, 'data.posts', []);

            $firstBannerPost = $bannerPosts[0] ?? null;

            $viewData = [
                'selectedRootCategory' => $selectedRootCategory,
                'childCategories' => $childCategories,
                'allProducts' => $allProducts,
                'firstBannerPost' => $firstBannerPost,

                'categorySettings' => [
                    'autoplay_speed' => data_get($bannerPostsData, 'data.category.icon', 3000),
                ],
                'isNewsPage' => true, // Flag để view biết đây là trang news
            ];

            // ✅ Sử dụng view category-news.blade.php
            return view('langding.category-news', $viewData);
        } catch (\Exception $e) {
            return view('langding.category-news', [
                'selectedRootCategory' => null,
                'childCategories' => [],
                'allProducts' => []
            ])->withErrors(['error' => 'Không thể tải dữ liệu. Vui lòng thử lại sau.']);
        }
    }
}
