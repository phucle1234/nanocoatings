<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Traits\CarSearch;
use App\Traits\HasBanners;
use App\Traits\HasImage;
use App\Traits\ProductSearchable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopController extends Controller
{
    use CarSearch, HasBanners, HasImage, ProductSearchable;

    protected ProductService $productService;
    protected ProductApiController $productApiController;
    protected CategoryApiController $categoryApiController;
    protected CategoryService $categoryService;

    public function __construct(
        ProductService $productService,
        ProductApiController $productApiController,
        CategoryApiController $categoryApiController,
        CategoryService $categoryService
    ) {
        $this->productService = $productService;
        $this->productApiController = $productApiController;
        $this->categoryApiController = $categoryApiController;
        $this->categoryService = $categoryService;
    }

    public function shop(Request $request)
    {
        $currentLocale = app()->getLocale();

        $filterSearch = $this->buildFilterSearch($request);
        $filterableAttributes = $this->getFilterableAttributes();
        $categoriesWithChildrenShop = $this->loadShopCategories($request);
        $homeSliderBannersShop = $this->getBannersBySlug('home-slider');

        if (!$request->has('per_page')) {
            $request->merge(['per_page' => 16]);
        }

        $response = $this->categoryApiController->allProducts($request);
        $productsData = json_decode($response->getContent(), true);

        $allProducts = [];
        $paginated = null;

        if (!empty($productsData['success'])) {
            $allProducts = $productsData['data']['products'] ?? [];
            $paginationData = $productsData['data']['pagination'] ?? null;

            $allProducts = $this->enrichProductsWithCategoryData($allProducts, $currentLocale);
            $paginated = $this->buildPaginator($allProducts, $paginationData, $request);
        }

        [
            'defaultMinPrice' => $defaultMinPrice,
            'defaultMaxPrice' => $defaultMaxPrice,
            'selectedMinPrice' => $selectedMinPrice,
            'selectedMaxPrice' => $selectedMaxPrice,
        ] = $this->resolvePriceRangeFromApi($request);

        $filterSearch['min_price'] = $selectedMinPrice;
        $filterSearch['max_price'] = $selectedMaxPrice;

        return view('langding.shop', compact(
            'categoriesWithChildrenShop',
            'homeSliderBannersShop',
            'allProducts',
            'filterSearch',
            'filterableAttributes',
            'paginated',
            'defaultMinPrice',
            'defaultMaxPrice',
            'selectedMinPrice',
            'selectedMaxPrice'
        ));
    }

    protected function buildFilterSearch(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 16);
        $perPage = in_array($perPage, [16, 24, 32], true) ? $perPage : 16;

        return [
            'sort_by' => $request->input('sort_by', 'price'),
            'per_page' => $perPage,
            'filter_type' => $request->input('filter_type', ''),
            'category_ids' => array_values(array_filter((array) $request->input('category_ids', []))),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'attribute_value_ids' => array_values(array_filter((array) $request->input('attribute_value_ids', []))),
        ];
    }

    protected function getFilterableAttributes()
    {
        return ProductAttribute::query()
            ->active()
            ->where('code', 'characteristic')
            ->ordered()
            ->with([
                'translation',
                'activeValues' => function ($query) {
                    $query->with('translation')->ordered();
                }
            ])
            ->get();
    }

    protected function loadShopCategories(Request $request): array
    {
        $response = $this->categoryApiController->root($request);
        $responseData = json_decode($response->getContent(), true);

        if (empty($responseData['success'])) {
            return [];
        }

        $categoriesWithChildrenShop = [];
        $categories = $responseData['data'] ?? [];

        foreach ($categories as $category) {
            $categoryData = $category;
            $categoryData['children'] = [];

            if (($category['children_count'] ?? 0) > 0) {
                $identifier = $category['slug'] ?? $category['id'];
                $childrenResponse = $this->categoryApiController->children($request, $identifier);
                $childrenData = json_decode($childrenResponse->getContent(), true);

                if (!empty($childrenData['success'])) {
                    $children = $childrenData['data']['children'] ?? [];

                    foreach ($children as $index => $child) {
                        $children[$index]['children'] = [];

                        if (($child['children_count'] ?? 0) > 0) {
                            $childIdentifier = $child['slug'] ?? $child['id'];
                            $grandchildrenResponse = $this->categoryApiController->children($request, $childIdentifier);
                            $grandchildrenData = json_decode($grandchildrenResponse->getContent(), true);

                            if (!empty($grandchildrenData['success'])) {
                                $children[$index]['children'] = $grandchildrenData['data']['children'] ?? [];
                            }
                        }
                    }

                    $categoryData['children'] = $children;
                }
            }

            $categoriesWithChildrenShop[] = $categoryData;
        }

        return $categoriesWithChildrenShop;
    }

    protected function enrichProductsWithCategoryData(array $products, string $currentLocale): array
    {
        foreach ($products as $index => $product) {
            $products[$index]['category_name'] = null;
            $products[$index]['category_slug'] = null;

            if (empty($product['category_id'])) {
                continue;
            }

            $category = $this->categoryService->getCategoryBySlugOrId($product['category_id'], $currentLocale);

            if (!$category) {
                continue;
            }

            $translation = $category->translations->firstWhere('language', $currentLocale);

            $products[$index]['category_name'] = $translation->name ?? null;
            $products[$index]['category_slug'] = $translation->slug ?? null;
        }

        return $products;
    }

    protected function buildPaginator(array $allProducts, ?array $paginationData, Request $request): ?LengthAwarePaginator
    {
        if (!$paginationData) {
            return null;
        }

        return new LengthAwarePaginator(
            items: $allProducts,
            total: $paginationData['total'] ?? 0,
            perPage: $paginationData['per_page'] ?? 16,
            currentPage: $paginationData['current_page'] ?? 1,
            options: [
                'path' => route('shop'),
                'query' => $request->query(),
            ]
        );
    }

    protected function resolvePriceRangeFromApi(Request $request): array
    {
        $defaultMinPrice = 100000;
        $defaultMaxPrice = 5000000;

        $minProduct = $this->fetchBoundaryProduct($request, 'price');
        $maxProduct = $this->fetchBoundaryProduct($request, 'price_desc');

        $minPrice = $this->extractProductPrice($minProduct);
        $maxPrice = $this->extractProductPrice($maxProduct);

        if (!is_null($minPrice)) {
            $defaultMinPrice = $minPrice;
        }

        if (!is_null($maxPrice)) {
            $defaultMaxPrice = $maxPrice;
        }

        if ($defaultMinPrice > $defaultMaxPrice) {
            $defaultMinPrice = 100000;
            $defaultMaxPrice = 5000000;
        }

        $selectedMinPrice = (int) $request->input('min_price', $defaultMinPrice);
        $selectedMaxPrice = (int) $request->input('max_price', $defaultMaxPrice);

        $selectedMinPrice = max($defaultMinPrice, min($selectedMinPrice, $defaultMaxPrice));
        $selectedMaxPrice = max($selectedMinPrice, min($selectedMaxPrice, $defaultMaxPrice));

        return [
            'defaultMinPrice' => $defaultMinPrice,
            'defaultMaxPrice' => $defaultMaxPrice,
            'selectedMinPrice' => $selectedMinPrice,
            'selectedMaxPrice' => $selectedMaxPrice,
        ];
    }

    protected function fetchBoundaryProduct(Request $request, string $sortBy): ?array
    {
        $query = $request->query();

        unset($query['min_price'], $query['max_price'], $query['page']);

        $query['sort_by'] = $sortBy;
        $query['per_page'] = 1;

        $fakeRequest = Request::create(
            $request->url(),
            'GET',
            $query
        );

        $response = $this->categoryApiController->allProducts($fakeRequest);
        $data = json_decode($response->getContent(), true);

        if (empty($data['success'])) {
            return null;
        }

        return $data['data']['products'][0] ?? null;
    }

    protected function extractProductPrice(?array $product): ?int
    {
        if (!$product) {
            return null;
        }

        $candidateKeys = [
            'price',
            'sale_price',
            'final_price',
            'selling_price',
            'price_value',
            'price_number',
        ];

        foreach ($candidateKeys as $key) {
            if (isset($product[$key]) && is_numeric($product[$key]) && (float) $product[$key] > 0) {
                return (int) round($product[$key]);
            }
        }

        if (!empty($product['price_display'])) {
            $priceDigits = preg_replace('/[^\d]/', '', (string) $product['price_display']);

            if (is_numeric($priceDigits) && (int) $priceDigits > 0) {
                return (int) $priceDigits;
            }
        }

        return null;
    }
}
