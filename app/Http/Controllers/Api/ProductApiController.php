<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductsByCategory(Request $request): JsonResponse
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
                'category_id' => 'required|integer|exists:product_categories,id',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'is_featured' => $booleanRule,
                'is_bestseller' => $booleanRule,
                'is_new' => $booleanRule,
                'sort_by' => 'nullable|string|in:sort_order,created_at,price,price_desc,name',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Lấy category theo ID
            $category = ProductCategory::with(['translations' => function ($query) use ($currentLocale) {
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

            // Prepare filters - sau khi validate boolean, chỉ cần check giá trị
            $filters = [];
            if ($request->boolean('is_featured')) {
                $filters['is_featured'] = true;
            }
            if ($request->boolean('is_bestseller')) {
                $filters['is_bestseller'] = true;
            }
            if ($request->boolean('is_new')) {
                $filters['is_new'] = true;
            }
            if ($request->filled('min_price')) {
                $filters['min_price'] = $request->min_price;
            }
            if ($request->filled('max_price')) {
                $filters['max_price'] = $request->max_price;
            }

            // Get products using service
            $sortBy = $request->get('sort_by', 'sort_order');
            $perPage = $request->get('per_page', 15);

            $result = $this->productService->getProductsByCategory(
                $category,
                $filters,
                $sortBy,
                $perPage,
                $currentLocale
            );

            $products = $result['products'];
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
                    'products' => $products,
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
                'message' => 'Lỗi khi lấy danh sách sản phẩm',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lấy chi tiết sản phẩm theo slug
     * 
     * @param string $slug
     * @return JsonResponse
     */
    public function getProductDetail(string $slug): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            $product = $this->productService->getProductBySlugOrId($slug, $currentLocale);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm'
                ], 404);
            }

            // Lấy sản phẩm liên quan
            $relatedProducts = $this->productService->getRelatedProducts(
                $product->category_id,
                $product->id,
                12,
                $currentLocale
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'related_products' => $relatedProducts
                ],
                'locale' => $currentLocale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết sản phẩm',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
