<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Services\CategoryService;
use App\Services\PostCategoryService;
use App\Services\PostService;
use App\Services\ProductService;
use App\Services\SectorLayoutService;
use App\Services\SectorService;
use App\Traits\CarSearch;
use App\Traits\HasBanners;
use App\Traits\HasImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SectorController extends Controller
{
    use CarSearch;
    use HasBanners;
    use HasImage;

    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected PostService $postService,
        protected PostCategoryService $postCategoryService,
        protected SectorService $sectorService,
        protected SectorLayoutService $sectorLayoutService
    ) {}

    public function index()
    {
        $sectors = $this->sectorService->getActiveSectors();
        $currentLocale = app()->getLocale();

        $sectors = $sectors->map(function (PostCategory $sector) use ($currentLocale) {
            $translation = $sector->translations->firstWhere('language', $currentLocale)
                ?? $sector->translations->first();
            $sector->display_name = $translation->name ?? 'N/A';
            $sector->display_description = $translation->description ?? '';
            $sector->display_slug = $translation->slug ?? '';
            $sector->cover_image = $this->getImageJson($sector->image_urls);

            return $sector;
        });

        return view('langding.sectors.index', compact('sectors'));
    }

    public function show(Request $request, string $slug)
    {
        $sector = $this->sectorService->findSectorBySlug($slug);
        if (! $sector) {
            throw new NotFoundHttpException;
        }

        $currentLocale = $this->applySectorDefaultLocale($request, $sector);
        $translation = $sector->translations->firstWhere('language', $currentLocale)
            ?? $sector->translations->first();

        $this->sectorService->syncSectorResources($sector);

        $sectorBlocks = $this->sectorLayoutService->getActiveLayoutBlocks($sector);
        $bannerData = $this->resolveSectorBannerData($sector);

        $bestsellerProducts = $this->productService->getBestsellerProducts(10, $currentLocale, $sector->id);
        $categories = $this->categoryService->getFeaturedCategories($currentLocale, $sector->id);
        $rootCategories = $this->categoryService->getRootCategories($currentLocale, $sector->id);

        foreach ($rootCategories as $category) {
            $category->category_image = $this->getImageJson($category->category_image_urls);
        }

        $allCategoryRows = $this->groupCategoriesInRows($rootCategories);

        foreach ($categories as $category) {
            $category->category_image = $this->getImageJson($category->category_image_urls);
        }

        $newsCategories = $this->loadNewsCategories($sector, $currentLocale);

        return view('langding.sectors.show', array_merge($bannerData, [
            'sector' => $sector,
            'sectorTranslation' => $translation,
            'sectorBlocks' => $sectorBlocks,
            'isSectorPage' => true,
            'bestsellerProducts' => $bestsellerProducts,
            'categories' => $categories,
            'rootCategories' => $rootCategories,
            'allCategoryRows' => $allCategoryRows,
            'newsCategories' => $newsCategories,
        ]));
    }

    protected function applySectorDefaultLocale(Request $request, PostCategory $sector): string
    {
        $supportedLocales = array_keys(config('languages.supported', []));
        $defaultLocale = $sector->default_locale;

        if (
            $defaultLocale
            && in_array($defaultLocale, $supportedLocales, true)
            && ! $request->route('locale')
            && ! $request->session()->get('locale_manually_selected')
        ) {
            App::setLocale($defaultLocale);
        }

        return app()->getLocale();
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveSectorBannerData(PostCategory $sector): array
    {
        $data = [];
        $bannerKeys = config('sector_layout.banner_keys', []);

        foreach ($bannerKeys as $bannerKey) {
            $slug = $this->sectorService->getBannerCategorySlug($sector, $bannerKey);
            $banners = $this->getBannersBySlug($slug);

            match ($bannerKey) {
                'home-slider' => $data['homeSliderBanners'] = $banners,
                'home-slider-2' => $data['homeSliderBanners2'] = $banners,
                'home-slider-3' => $data['homeSliderBanners3'] = $banners,
                'home-promotion' => $data['promotionBanners'] = $banners,
                'video-introduction' => $data['introductionBanners'] = $banners,
                'partner-banner' => $data['partnerBanners'] = $banners,
                'home-category' => $data['categoryBlockBanners'] = $banners,
                'home-bestseller' => $data['bestsellerBlockBanners'] = $banners,
                'home-media' => $data['mediaBlockBanners'] = $banners,
                'footer-main' => $data['footerBlockBanners'] = $banners,
                default => null,
            };
        }

        return $data;
    }

    protected function loadNewsCategories(PostCategory $sector, string $currentLocale)
    {
        $hubSlug = $this->sectorService->getNewsHubSlug($sector, $currentLocale);
        $newsHub = $this->postCategoryService->getCategoryBySlugOrId($hubSlug, $currentLocale)
            ?? $this->postCategoryService->getCategoryBySlugOrId(
                $this->sectorService->getNewsHubSlug($sector, 'vi'),
                $currentLocale
            );
        $newsCategories = collect();

        if (! $newsHub) {
            return $newsCategories;
        }

        $children = $newsHub->children()
            ->where('is_active', true)
            ->withCount('posts as posts_count')
            ->with(['translations' => function ($q) use ($currentLocale) {
                $q->where('language', $currentLocale);
            }])
            ->orderBy('sort_order')
            ->get();

        $newsCategories = $children->filter(function ($category) use ($currentLocale) {
            $translation = $category->translations->firstWhere('language', $currentLocale);
            $excludedSlugs = [
                'banners-home',
                'home-slider',
                'home-promotion',
                'video-introduction',
                'partner-banner',
            ];

            return ! in_array($translation->slug ?? null, $excludedSlugs);
        })->map(function ($category) use ($currentLocale) {
            $translation = $category->translations->firstWhere('language', $currentLocale);
            $category->category_name = $translation->name ?? null;
            $category->category_description = $translation->description ?? null;

            return $category;
        });

        foreach ($newsCategories as $category) {
            if (! $category instanceof PostCategory) {
                continue;
            }
            $result = $this->postService->getPostsByCategory($category, [], 'sort_order', 4, $currentLocale, 1);
            $p = $result['pagination'];
            $category->feed_posts = $result['posts']->values()->all();
            $category->feed_pagination = [
                'current_page' => $p->currentPage(),
                'last_page' => max(1, $p->lastPage()),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
            ];
        }

        return $newsCategories;
    }

    private function groupCategoriesInRows($categories)
    {
        $categoriesPerRow = 6;
        $rows = [];
        $currentRow = [];
        $index = 0;
        $total = is_countable($categories) ? count($categories) : $categories->count();

        foreach ($categories as $category) {
            $currentRow[] = $category;
            $index++;

            if ($index % $categoriesPerRow == 0 || $index == $total) {
                $rows[] = $currentRow;
                $currentRow = [];
            }
        }

        return $rows;
    }
}
