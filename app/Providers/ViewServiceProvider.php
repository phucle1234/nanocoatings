<?php

namespace App\Providers;

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Models\Cart;
use App\Services\SectorService;
use App\Traits\CartManagement;
use App\Traits\HasBanners;
use App\Traits\HasImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    use CartManagement, HasBanners, HasImage;

    /**
     * The current sector, if the page currently being rendered is a sector
     * page (route `sectors.show`), otherwise null (homepage / any other page).
     */
    private function currentSector(): ?\App\Models\PostCategory
    {
        $route = request()->route();

        if (! $route || $route->getName() !== 'sectors.show') {
            return null;
        }

        $slug = $route->parameter('slug');
        if (! $slug) {
            return null;
        }

        return app(SectorService::class)->findSectorBySlug($slug);
    }

    /**
     * The current sector's PostCategory id, if the page currently being
     * rendered is a sector page (route `sectors.show`), otherwise null
     * (homepage / any other page).
     */
    private function currentSectorId(): ?int
    {
        return $this->currentSector()?->id;
    }

    /**
     * Resolve a banner category slug for the given literal (homepage) key,
     * scoped to the current sector when one is active — e.g. 'footer-lien-he'
     * becomes 'sector-vat-lieu-go-footer-lien-he' on that sector's page.
     * Falls back to the literal slug on the homepage / any non-sector page.
     */
    private function sectorAwareBannerSlug(string $literalSlug): string
    {
        $sector = $this->currentSector();

        if (! $sector) {
            return $literalSlug;
        }

        return app(SectorService::class)->getBannerCategorySlug($sector, $literalSlug);
    }

    /**
     * Phone-number lines from the current sector's own contact-info banner
     * category (falls back to the homepage's own on non-sector pages) —
     * reused by both the header hotline display and the floating sidebar's
     * "Liên hệ" box, so a sector's phone number is no longer hardcoded HTML
     * shared identically across every page.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function contactPhoneNumbers(): \Illuminate\Support\Collection
    {
        $lienHe = $this->getBannersBySlug($this->sectorAwareBannerSlug('footer-lien-he'));

        return collect($lienHe['banners'] ?? [])
            ->filter(function ($banner) {
                $image = $banner->image ?? '';

                return str_contains($image, 'telephone') || str_contains($image, 'phone-call') || str_contains($image, '/phone');
            })
            ->values();
    }

    public function boot()
    {
        View::composer('langding.components.header', function ($view) {
            $categoryApiController = app(CategoryApiController::class);
            $postCategoryApiController = app(PostCategoryApiController::class);
            $sectorId = $this->currentSectorId();
            $request = new Request($sectorId !== null ? ['sector_id' => $sectorId] : []);

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

            $caseStudyPosts = [];
            $HeThongPhanPhoiCategories = [];
            $gioiThieuCasuminaPosts = [];
            $truyenThongCategories = [];
            try {
                // Case Study: menu con = bài viết trong danh mục (không phải subcategory)
                $caseStudyRequest = new Request(['per_page' => 50]);
                $caseStudyResponse = $postCategoryApiController->posts($caseStudyRequest, 'case-study');

                $caseStudyData = json_decode($caseStudyResponse->getContent(), true);
                if ($caseStudyData['success'] ?? false) {
                    $caseStudyPosts = $caseStudyData['data']['posts'] ?? [];
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

                $truyenThongResponse = $postCategoryApiController->show(new Request, 'truyen-thong');
                $truyenThongData = json_decode($truyenThongResponse->getContent(), true);

                if ($truyenThongData['success'] && isset($truyenThongData['data']['children'])) {
                    $truyenThongCategories = $truyenThongData['data']['children'];
                }
            } catch (\Exception $e) {
                $caseStudyPosts = [];
                $HeThongPhanPhoiCategories = [];
                $gioiThieuCasuminaPosts = [];
                $truyenThongCategories = [];
            }

            // /sản phầm
            $view->with('menuCategories', $categoriesWithChildren);
            // /đăng kiêm
            $view->with('caseStudyPosts', $caseStudyPosts);
            // /hệ thống phân phối
            $view->with('HeThongPhanPhoiCategories', $HeThongPhanPhoiCategories);

            $view->with('cartCount', $cartCount);

            $view->with('gioiThieuCasuminaPosts', $gioiThieuCasuminaPosts);

            $view->with('truyenThongCategories', $truyenThongCategories);

            $view->with('headerPhones', $this->contactPhoneNumbers());
        });

        // Floating sidebar (langding.index itself, not a sub-partial) — same
        // sector-aware phone numbers as the header, for the "Liên hệ" box.
        View::composer('langding.index', function ($view) {
            if ($view->offsetExists('sidebarPhones')) {
                return;
            }
            $view->with('sidebarPhones', $this->contactPhoneNumbers());
        });

        // Promotion banners - Dùng chung cho nhiều trang
        View::composer('langding.components.promotion-slide', function ($view) {
            if ($view->offsetExists('promotionBanners')) {
                return;
            }
            $promotionBanners = $this->getBannersBySlug('home-promotion');
            $view->with('promotionBanners', $promotionBanners);
        });

        // Partner banners - Dùng chung cho nhiều trang
        View::composer('langding.components.partner-slide', function ($view) {
            if ($view->offsetExists('partnerBanners')) {
                return;
            }
            $partnerBanners = $this->getBannersBySlug('partner-banner');
            $view->with('partnerBanners', $partnerBanners);
        });

        // Video - Dùng chung cho nhiều trang
        View::composer('langding.components.video', function ($view) {
            if ($view->offsetExists('introductionBanners')) {
                return;
            }
            $introductionBanners = $this->getBannersBySlug('video-introduction');
            $view->with('introductionBanners', $introductionBanners);
        });

        View::composer('langding.components.footer', function ($view) {
            $categoryApiController = app(CategoryApiController::class);
            $sectorId = $this->currentSectorId();
            $request = new Request($sectorId !== null ? ['sector_id' => $sectorId] : []);

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
            $lienHe = $this->getBannersBySlug($this->sectorAwareBannerSlug('footer-lien-he'));
            $veCasumina = $this->getBannersBySlug($this->sectorAwareBannerSlug('footer-ve-nanocoatings'), false);
            $ketNoiVoiCasumina = $this->getBannersBySlug($this->sectorAwareBannerSlug('ket-noi-voi-casumina'));
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
            $request = new Request;
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
