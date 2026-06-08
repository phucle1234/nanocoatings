<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Traits\CartManagement;
use Illuminate\Http\Request;
use App\Traits\HasBanners;
use App\Traits\HasImage;
use App\Services\PostCategoryService;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
class ViewServiceProvider extends ServiceProvider
{
    use HasBanners, HasImage, CartManagement;

    public function boot()
    {
        View::composer('langding.components.header', function ($view) {
            $categoryApiController = app(CategoryApiController::class);
            $postCategoryApiController = app(PostCategoryApiController::class);
            $request = new Request();

            // Lấy số lượng giỏ hàng
            if (Auth::check()) {
                $cart = Cart::getCart(Auth::id(), 'customer');
                $cartCount = (int) ($cart->item_count ?? 0);
            } else {
                $cartCount = $this->getCartTotalQuantity();
            }


            // Gọi API root categories
            $response = $categoryApiController->root($request);
            $responseData = json_decode($response->getContent(), true);
            $categoriesWithChildren = [];

            if ($responseData['success'] ?? false) {
                $categories = $responseData['data'] ?? [];

                // Load children cho từng category
                foreach ($categories as $category) {
                    $categoryData = $category;

                    if (($category['children_count'] ?? 0) > 0) {
                        $identifier = $category['slug'] ?? $category['id'];
                        $childrenResponse = $categoryApiController->children($request, $identifier);
                        $childrenData = json_decode($childrenResponse->getContent(), true);

                        if ($childrenData['success'] ?? false) {
                            $children = $childrenData['data']['children'] ?? [];

                            // ✅ THÊM: Load grandchildren cho từng child có children_count > 0
                            foreach ($children as $index => $child) {
                                if (($child['children_count'] ?? 0) > 0) {
                                    $childIdentifier = $child['slug'] ?? $child['id'];
                                    $grandchildrenResponse = $categoryApiController->children($request, $childIdentifier);
                                    $grandchildrenData = json_decode($grandchildrenResponse->getContent(), true);

                                    if ($grandchildrenData['success'] ?? false) {
                                        $children[$index]['children'] = $grandchildrenData['data']['children'] ?? [];
                                    } else {
                                        $children[$index]['children'] = [];
                                    }
                                } else {
                                    $children[$index]['children'] = [];
                                }
                            }

                            $categoryData['children'] = $children;
                        } else {
                            $categoryData['children'] = [];
                        }
                    } else {
                        $categoryData['children'] = [];
                    }

                    $categoriesWithChildren[] = $categoryData;
                }
            }


            $dangKiemCategories = [];
            $HeThongPhanPhoiCategories = [];
            $gioiThieuCasuminaPosts = [];
            $truyenThongCategories = [];
            try {
                // ✅ Lấy children của "Thông tin đăng kiểm" (post category)
                $dangKiemResponse = $postCategoryApiController->show($request, 'thong-tin-dang-kiem');
                $dangKiemData = json_decode($dangKiemResponse->getContent(), true);
                if ($dangKiemData['success'] ?? false) {
                    $dangKiemCategory = $dangKiemData['data'];

                    if (($dangKiemCategory['children'] ?? 0) > 0) {
                        $identifier = $dangKiemCategory['slug'] ?? $dangKiemCategory['id'];
                        $childrenResponse = $postCategoryApiController->children($request, $identifier);
                        $childrenData = json_decode($childrenResponse->getContent(), true);

                        if ($childrenData['success'] ?? false) {
                            $children = $childrenData['data']['children'] ?? [];

                            foreach ($children as $index => $child) {
                                if (($child['id'] ?? null) != null) {
                                    $childIdentifier = $child['slug'] ?? $child['id'];
                                    $grandchildrenResponse = $postCategoryApiController->children($request, $childIdentifier);
                                    $grandchildrenData = json_decode($grandchildrenResponse->getContent(), true);

                                    if ($grandchildrenData['success'] ?? false) {
                                        $children[$index]['children'] = $grandchildrenData['data']['children'] ?? [];
                                    } else {
                                        $children[$index]['children'] = [];
                                    }
                                } else {
                                    $children[$index]['children'] = [];
                                }
                            }

                            $dangKiemCategories = $children;
                        }
                    }
                }

                // ✅ Lấy children của "Hệ thống phân phối" (post category)
                $HeThongPhanPhoiResponse = $postCategoryApiController->show($request, 'he-thong-phan-phoi');
                $HeThongPhanPhoiData = json_decode($HeThongPhanPhoiResponse->getContent(), true);
                if ($HeThongPhanPhoiData['success'] ?? false) {
                    $HeThongPhanPhoiCategory = $HeThongPhanPhoiData['data'];

                    if (($HeThongPhanPhoiCategory['children'] ?? 0) > 0) {
                        $identifier = $HeThongPhanPhoiCategory['slug'] ?? $HeThongPhanPhoiCategory['id'];
                        $childrenResponse = $postCategoryApiController->children($request, $identifier);
                        $childrenData = json_decode($childrenResponse->getContent(), true);

                        if ($childrenData['success'] ?? false) {
                            $children = $childrenData['data']['children'] ?? [];

                            foreach ($children as $index => $child) {
                                if (($child['id'] ?? null) != null) {
                                    $childIdentifier = $child['slug'] ?? $child['id'];
                                    $grandchildrenResponse = $postCategoryApiController->children($request, $childIdentifier);
                                    $grandchildrenData = json_decode($grandchildrenResponse->getContent(), true);

                                    if ($grandchildrenData['success'] ?? false) {
                                        $children[$index]['children'] = $grandchildrenData['data']['children'] ?? [];
                                    } else {
                                        $children[$index]['children'] = [];
                                    }
                                } else {
                                    $children[$index]['children'] = [];
                                }
                            }

                            $HeThongPhanPhoiCategories = $children;
                        }
                    }
                }

                // Lấy tất cả posts của danh mục "Giới thiệu Casumina"
                $postsResponse = $postCategoryApiController->posts($request, 'gioi-thieu-cong-ty');
                $decoded = json_decode($postsResponse->getContent(), true);
                if ($decoded && ($decoded['success'] ?? false)) {
                    $gioiThieuCasuminaPosts = $decoded['data']['posts'] ?? [];
                } else {
                    $gioiThieuCasuminaPosts = []; // ← luôn reset về array rỗng nếu thất bại
                }

                $truyenThongResponse = $postCategoryApiController->show(new Request(), 'truyen-thong');
                $truyenThongData = json_decode($truyenThongResponse->getContent(), true);

                if ($truyenThongData['success'] && isset($truyenThongData['data']['children'])) {
                    $truyenThongCategories = $truyenThongData['data']['children'];
                }
            } catch (\Exception $e) {
                $dangKiemCategories = [];
                $HeThongPhanPhoiCategories = [];
                $gioiThieuCasuminaPosts = [];
                $truyenThongCategories = [];
            }

            ///sản phầm
            $view->with('menuCategories', $categoriesWithChildren);
            ///đăng kiêm
            $view->with('dangKiemCategories', $dangKiemCategories);
            ///hệ thống phân phối
            $view->with('HeThongPhanPhoiCategories', $HeThongPhanPhoiCategories);

            $view->with('cartCount', $cartCount);

            $view->with('gioiThieuCasuminaPosts', $gioiThieuCasuminaPosts);

            $view->with('truyenThongCategories', $truyenThongCategories);
        });

        // Promotion banners - Dùng chung cho nhiều trang
        View::composer('langding.components.promotion-slide', function ($view) {
            $promotionBanners = $this->getBannersBySlug('home-promotion');
            $view->with('promotionBanners', $promotionBanners);
        });

        // Partner banners - Dùng chung cho nhiều trang
        View::composer('langding.components.partner-slide', function ($view) {
            $partnerBanners = $this->getBannersBySlug('partner-banner');
            $view->with('partnerBanners', $partnerBanners);
        });

        // Video - Dùng chung cho nhiều trang
        View::composer('langding.components.video', function ($view) {
            $introductionBanners = $this->getBannersBySlug('video-introduction');
            $view->with('introductionBanners', $introductionBanners);
        });


        View::composer('langding.components.footer', function ($view) {
            $categoryApiController = app(CategoryApiController::class);
            $request = new Request();

            // Gọi API root categories
            $response = $categoryApiController->root($request);
            $responseData = json_decode($response->getContent(), true);
            if ($responseData['success'] ?? false) {
                $categories = $responseData['data'] ?? [];
                $view->with('footerCategories', $categories);
            } else {
                $view->with('footerCategories', []);
            }

            $footerMain = $this->getBannersBySlug('footer-main');
            $footerMainCategory = $footerMain['category'];
            if ($footerMainCategory) {
                $footerMainCategory->image = $footerMain['category_bg_image'] ?? null;
            }
            $lienHe = $this->getBannersBySlug('footer-lien-he');
            $veCasumina = $this->getBannersBySlug('footer-ve-nanocoatings', false);
            $ketNoiVoiCasumina = $this->getBannersBySlug('ket-noi-voi-casumina');
            $view->with('lienHe', $lienHe);
            $view->with('veCasumina', $veCasumina);
            $view->with('ketNoiVoiCasumina', $ketNoiVoiCasumina);
            $view->with('footerMain', $footerMainCategory);
        });

        View::composer('langding.components.daily', function ($view) {
            if (app()->getLocale() === 'vi') {
                $slug2 = 'he-thong-phan-phoi';
                $slugQuocGia = 'quoc-gia-he-thong-phan-phoi';
            } else {
                $slug2 = 'distribution-system';
                $slugQuocGia = 'international-distribution-system';
            }
            $request = new Request();
            $postCategoryApiController = app(PostCategoryApiController::class);


            $parentCategoryResponse = $postCategoryApiController->show($request, $slug2);
            $parentCategoryData = json_decode($parentCategoryResponse->getContent(), true);
            $childCategories = $parentCategoryData['data']['children'] ?? [];


            $QuocGiaResponse = $postCategoryApiController->show($request, $slugQuocGia);
            $QuocGiaCategoryData = json_decode($QuocGiaResponse->getContent(), true);
            $childQuocGia = $QuocGiaCategoryData['data']['children'] ?? [];

            $paginationRequest = new Request([
                'per_page' => 50,
            ]);

            $TinhThanhResponse = $postCategoryApiController->posts($paginationRequest, 'viet-nam');
            $TinhThanhData = json_decode($TinhThanhResponse->getContent(), true);
            $childTinhThanh = $TinhThanhData['data']['posts'] ?? [];

            $locale = app()->getLocale();
            $nameField = $locale === 'vi' ? 'name_vi' : 'name_en';
            $countries = DB::table('npp_countries')
                ->select('id', 'name_vi', 'name_en', 'code', 'phone_code', 'region', 'latitude', 'longitude')
                ->orderByRaw("CASE WHEN code = 'VN' THEN 0 ELSE 1 END")
                ->orderBy($nameField)
                ->get();

            $view->with('childTinhThanh', $childTinhThanh);
            $view->with('childQuocGia', $childQuocGia);
            $view->with('childCategories', $childCategories);
            $view->with('countries', $countries);
        });
    }
}
