<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Services\PostService;
use App\Traits\HasImage;
use App\Traits\CarSearch;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    use HasImage;
    use CarSearch;

    private const AWARD_IMAGES_PER_PAGE = 8;

    protected $postCategoryApiController;
    protected $postApiController;

    public function __construct(
        PostCategoryApiController $postCategoryApiController,
        PostApiController $postApiController,
        protected PostService $postService
    ) {
        $this->postCategoryApiController = $postCategoryApiController;
        $this->postApiController = $postApiController;
    }

    public function index(Request $request)
    {
        try {
            // Lấy danh mục "Giới thiệu Casumina"
            $categoryResponse = $this->postCategoryApiController->show($request, 'gioi-thieu-cong-ty');
            $categoryData = json_decode($categoryResponse->getContent(), true);

            if (!$categoryData['success']) {
                return redirect()
                    ->route('home')
                    ->with('error', 'Không tìm thấy danh mục giới thiệu');
            }

            $request->merge(['sort_by' => 'sort_order']);

            // Lấy tất cả posts của danh mục "Giới thiệu Casumina"
            $postsResponse = $this->postCategoryApiController->posts($request, 'gioi-thieu-cong-ty');
            $postsData = json_decode($postsResponse->getContent(), true);

            if (!$postsData['success']) {
                return redirect()
                    ->route('home')
                    ->with('error', 'Có lỗi xảy ra khi tải bài viết');
            }

            $posts = $postsData['data']['posts'] ?? [];

            // Lấy posts của danh mục "Thư viện" và "Cộng đồng" (nếu có)
            $libraryPosts = [];
            $communityPosts = [];
            $awardPosts = [];
            $awardTiles = [];
            $awardCategoryId = null;
            $awardPagination = null;

            // Tìm danh mục con "Thư viện" và "Cộng đồng"
            $childrenResponse = $this->postCategoryApiController->children($request, 'gioi-thieu-cong-ty');
            $childrenData = json_decode($childrenResponse->getContent(), true);

            if ($childrenData['success']) {
                $children = $childrenData['data']['children'] ?? [];

                foreach ($children as $child) {
                    $childSlug = $child['slug'] ?? '';

                    if ($childSlug === 'thu-vien') {
                        $libraryResponse = $this->postCategoryApiController->posts($request, $child['id']);
                        $libraryData = json_decode($libraryResponse->getContent(), true);
                        if ($libraryData['success']) {
                            $libraryPosts = $libraryData['data']['posts'] ?? [];
                        }
                    } elseif ($childSlug === 'cong-dong') {
                        $communityResponse = $this->postCategoryApiController->posts($request, $child['id']);
                        $communityData = json_decode($communityResponse->getContent(), true);
                        if ($communityData['success']) {
                            $communityPosts = $communityData['data']['posts'] ?? [];
                        }
                    } elseif ($childSlug === 'giai-thuong' || stripos($childSlug, 'giai-thuong') !== false) {
                        $awardCategoryId = $child['id'];
                        $awardPage = $this->postService->getAwardGalleryImagePage(
                            (int) $child['id'],
                            1,
                            self::AWARD_IMAGES_PER_PAGE,
                            app()->getLocale()
                        );
                        $awardTiles = $awardPage['tiles'];
                        $awardPagination = $awardPage['pagination'];
                        $awardPosts = [];
                    }
                }
            }

            return view('langding.about', [
                'categories' => $posts,
                'libraryPosts' => $libraryPosts,
                'communityPosts' => $communityPosts,
                'awardPosts' => $awardPosts,
                'awardTiles' => $awardTiles,
                'awardCategoryId' => $awardCategoryId,
                'awardPagination' => $awardPagination,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('home')
                ->with('error', 'Có lỗi xảy ra khi tải giới thiệu công ty');
        }
    }
}
