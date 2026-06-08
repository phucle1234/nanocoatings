<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Traits\HasImage;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

class CategoryApiController extends Controller
{
    use HasImage;

    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * Lấy danh sách tất cả danh mục sản phẩm
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

            $query = ProductCategory::query()
                ->active()
                ->withCount('productsManyToMany as products_count')
                ->with(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }]);

            // Filter by parent_id
            if ($request->has('parent_id')) {
                $parentId = $request->parent_id;
                if ($parentId === 'null' || $parentId === null || $parentId === '') {
                    $query->root();
                } elseif (is_numeric($parentId)) {
                    $query->where('parent_id', (int) $parentId);
                }
            }

            // Filter by featured - sau khi validate boolean, chỉ cần check giá trị
            if ($request->boolean('is_featured')) {
                $query->featured();
            }

            $categories = $query->ordered()
                ->get()
                ->map(function ($category) use ($currentLocale, $request) {
                    $translation = $category->translations->firstWhere('language', $currentLocale);

                    $data = [
                        'id' => $category->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                        'description' => $translation->description ?? null,
                        'icon' => $category->icon,
                        'image' => $this->getImageJson($category->image_urls),
                        'parent_id' => $category->parent_id,
                        'is_featured' => $category->is_featured,
                        'sort_order' => $category->sort_order,
                        'products_count' => $this->categoryService->getProductsCount($category),
                    ];

                    // Include children if requested
                    if ($request->has('include_children') && $request->include_children) {
                        $data['children'] = $category->children()
                            ->active()
                            ->withCount('productsManyToMany as products_count')
                            ->with(['translations' => function ($q) use ($currentLocale) {
                                $q->where('language', $currentLocale);
                            }])
                            ->ordered()
                            ->get()
                            ->map(function ($child) use ($currentLocale) {
                                $childTranslation = $child->translations->firstWhere('language', $currentLocale);
                                return [
                                    'id' => $child->id,
                                    'slug' => $childTranslation->slug ?? null,
                                    'name' => $childTranslation->name ?? 'N/A',
                                    'description' => $childTranslation->description ?? null,
                                    'icon' => $child->icon,
                                    'image' => $this->getImageJson($child->image_urls),
                                    'parent_id' => $child->parent_id,
                                    'is_featured' => $child->is_featured,
                                    'sort_order' => $child->sort_order,
                                    'products_count' => $this->categoryService->getProductsCount($child),
                                ];
                            });
                    }

                    return $data;
                });

            return response()->json([
                'success' => true,
                'data' => $categories,
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
     * Lấy chi tiết một danh mục sản phẩm
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @return JsonResponse
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            $category = $this->categoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Ensure translations and products_count are loaded
            if (!$category->relationLoaded('translations')) {
                $category->load(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }]);
            }

            // Load products_count if not already loaded
            if (!isset($category->products_count)) {
                $category->loadCount('productsManyToMany as products_count');
            }

            $translation = $category->translations->firstWhere('language', $currentLocale);
            $parent = $category->parent;

            // Load parent translations if parent exists
            if ($parent) {
                if (!$parent->relationLoaded('translations')) {
                    $parent->load(['translations' => function ($q) use ($currentLocale) {
                        $q->where('language', $currentLocale);
                    }]);
                }
                $parentTranslation = $parent->translations->firstWhere('language', $currentLocale);
            } else {
                $parentTranslation = null;
            }

            // Load children
            $children = $category->children()
                ->active()
                ->withCount('productsManyToMany as products_count')
                ->with(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }])
                ->ordered()
                ->get()
                ->map(function ($child) use ($currentLocale) {
                    $childTranslation = $child->translations->firstWhere('language', $currentLocale);
                    return [
                        'id' => $child->id,
                        'slug' => $childTranslation->slug ?? null,
                        'name' => $childTranslation->name ?? 'N/A',
                        'description' => $childTranslation->description ?? null,
                        'icon' => $child->icon,
                        'image' => $this->getImageJson($child->image_urls),
                        'products_count' => $this->categoryService->getProductsCount($child),
                    ];
                });

            // Get path (breadcrumb) - reload with translations
            $pathIds = collect($category->getPath())->pluck('id')->toArray();
            $pathCategories = ProductCategory::whereIn('id', $pathIds)
                ->with(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }])
                ->get()
                ->sortBy(function ($cat) use ($pathIds) {
                    return array_search($cat->id, $pathIds);
                });

            $path = $pathCategories->map(function ($pathCategory) use ($currentLocale) {
                $pathTranslation = $pathCategory->translations->firstWhere('language', $currentLocale);
                return [
                    'id' => $pathCategory->id,
                    'code' => $pathCategory->code,
                    'slug' => $pathTranslation ? $pathTranslation->slug : $pathCategory->slug,
                    'name' => $pathTranslation ? $pathTranslation->name : 'N/A',
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'code' => $category->code,
                    'slug' => $translation->slug ?? $category->slug,
                    'name' => $translation->name ?? 'N/A',
                    'description' => $translation->description ?? null,
                    'icon' => $category->icon,
                    'meta_title' => $translation->meta_title,
                    'meta_description' => $translation->meta_description,
                    'image' => $this->getImageJson($translation->image_urls ?? $category->image_urls),
                    'all_images' => $this->getAllImagesJson($translation->image_urls ?? $category->image_urls),
                    'parent_id' => $category->parent_id,
                    'parent' => $parent ? [
                        'id' => $parent->id,
                        'code' => $parent->code,
                        'slug' => $parentTranslation->slug ?? null,
                        'name' => $parentTranslation->name ?? 'N/A',
                    ] : null,
                    'children' => $children,
                    'path' => $path,
                    'is_featured' => $category->is_featured,
                    'sort_order' => $category->sort_order,
                    'products_count' => $this->categoryService->getProductsCount($category),
                    'meta_keywords' => $category->meta_keywords,
                ],
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
     * Lấy danh sách sản phẩm trong danh mục
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @return JsonResponse
     */
    public function products(Request $request, $identifier): JsonResponse
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

            $category = $this->categoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $translation = $category->translations->firstWhere('language', $currentLocale);

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
            if ($request->has('min_price')) {
                $filters['min_price'] = $request->min_price;
            }
            if ($request->has('max_price')) {
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
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
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
     * Lấy danh sách tất cả sản phẩm (không theo danh mục)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function allProducts(Request $request): JsonResponse
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
                'is_bestseller' => $booleanRule,
                'is_new' => $booleanRule,
                'sort_by' => 'nullable|string|in:sort_order,created_at,price,price_desc,name',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'search' => 'nullable|string|max:255',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:product_categories,id',
                'attribute_value_ids' => 'nullable|array',
                'attribute_value_ids.*' => 'integer|exists:product_attribute_values,id',
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
            if ($request->filled('category_ids')) {
                $filters['category_ids'] = array_filter((array) $request->input('category_ids'));
            }
            if ($request->boolean('is_featured')) {
                $filters['is_featured'] = true;
            }
            if ($request->boolean('is_bestseller')) {
                $filters['is_bestseller'] = true;
            }
            if ($request->boolean('is_new')) {
                $filters['is_new'] = true;
            }
            if ($request->has('min_price')) {
                $filters['min_price'] = $request->min_price;
            }
            if ($request->has('max_price')) {
                $filters['max_price'] = $request->max_price;
            }
            if ($request->has('search')) {
                $filters['search'] = $request->search;
            }
            if ($request->filled('attribute_value_ids')) {
                $filters['attribute_value_ids'] = array_filter((array) $request->input('attribute_value_ids'));
            }

            // Get products using service
            $sortBy = $request->get('sort_by', 'sort_order');
            $perPage = $request->get('per_page', 15);

            $result = $this->productService->getAllProductsWithFilters(
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
                    'products' => $products,
                    'paginated' => $paginated,
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
     * Lấy danh sách sản phẩm nổi bật trong danh mục
     * 
     * @param Request $request
     * @param string|int $identifier Slug hoặc ID của danh mục
     * @return JsonResponse
     */
    public function featuredProducts(Request $request, $identifier): JsonResponse
    {
        try {
            $currentLocale = app()->getLocale();

            // Validate request
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|string|in:sort_order,created_at,price,price_desc,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = $this->categoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $translation = $category->translations->firstWhere('language', $currentLocale);

            // ✅ Lọc sản phẩm nổi bật (is_featured = true)
            $filters = [
                'is_featured' => true
            ];

            // Get products using service
            $sortBy = $request->get('sort_by', 'sort_order');
            $perPage = $request->get('per_page', 8); // Mặc định lấy 8 sản phẩm nổi bật

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
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
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
                'message' => 'Lỗi khi lấy danh sách sản phẩm nổi bật',
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

            $categories = ProductCategory::query()
                ->active()
                ->featured()
                ->withCount('productsManyToMany as products_count')
                ->with(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }])
                ->ordered()
                ->get()
                ->map(function ($category) use ($currentLocale) {
                    $translation = $category->translations->firstWhere('language', $currentLocale);

                    return [
                        'id' => $category->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                        'description' => $translation->description ?? null,
                        'icon' => $category->icon,
                        'image' => $this->getImageJson($category->image_urls),
                        'products_count' => $this->categoryService->getProductsCount($category),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories,
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

            $categories = ProductCategory::query()
                ->active()
                ->root()
                ->withCount('productsManyToMany as products_count')
                ->withCount(['children as children_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->with(['translations' => function ($q) use ($currentLocale) {
                    $q->where('language', $currentLocale);
                }])
                ->ordered()
                ->get()
                ->map(function ($category) use ($currentLocale) {
                    $translation = $category->translations->firstWhere('language', $currentLocale);

                    return [
                        'id' => $category->id,
                        'slug' => $translation->slug ?? null,
                        'name' => $translation->name ?? 'N/A',
                        'description' => $translation->description ?? null,
                        'icon' => $category->icon,
                        'image' => $this->getImageJson($category->image_urls),
                        'products_count' => $this->categoryService->getProductsCount($category),
                        'children_count' => (int) ($category->children_count ?? 0),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories,
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

            $category = $this->categoryService->getCategoryBySlugOrId($identifier, $currentLocale);

            if (!$category || !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // ✅ Dùng Service thay vì duplicate logic
            $children = $this->categoryService->getChildCategories($category, $currentLocale);

            // Format cho API response
            $formattedChildren = $children->map(function ($child) use ($currentLocale) {
                $translation = $child->translations->firstWhere('language', $currentLocale);
                return [
                    'id' => $child->id,
                    'slug' => $child->category_translation_slug ?? $child->slug,
                    'name' => $child->category_name ?? 'N/A',
                    'description' => $child->category_description ?? null,
                    'icon' => $child->icon,
                    'meta_title' => $translation->meta_title ?? null,
                    'meta_description' => $translation->meta_description ?? null,
                    'image' => $this->getImageJson($child->category_image_urls),
                    'link_type' => $child->link_type ?? 'detail',
                    'youtube_url' => $child->youtube_url ?? null,
                    'products_count' => $this->categoryService->getProductsCount($child),
                    'children_count' => (int) ($child->children_count ?? 0), // ✅ THÊM DÒNG NÀY
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'parent' => [
                        'id' => $category->id,
                        'slug' => $category->translations->firstWhere('language', $currentLocale)?->slug ?? null,
                        'name' => $category->translations->firstWhere('language', $currentLocale)->name ?? 'N/A',
                    ],
                    'children' => $formattedChildren
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
}
