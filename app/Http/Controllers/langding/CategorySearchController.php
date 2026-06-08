<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\CarSearch;
use App\Traits\HasImage;
use App\Traits\ProductSearchable;
use App\Models\ProductCategory;
use App\Models\Product;

class CategorySearchController extends Controller
{
    use CarSearch;
    use HasImage;
    use ProductSearchable;

    public function index(Request $request)
    {
        $currentLocale = app()->getLocale();
        $carSearchData = $this->getCarSearchData();

        // ✅ Lấy search query từ URL
        $searchQuery = $request->get('search');
        $searchType = $request->get('type', 'text');
        $searchTotal = 0;

        // ✅ Nếu có search query → Search và hiển thị kết quả
        $productIds = [];
        if (!empty($searchQuery)) {
            $results = $this->performSearch($searchQuery, $searchType);
            Log::info('Search results:', ['query' => $searchQuery, 'count' => count($results), 'results' => $results]);

            if (!empty($results)) {
                $searchTotal = count($results);

                // Lấy product IDs và giữ thứ tự từ search results
                foreach ($results as $result) {
                    if (isset($result['tire']['id'])) {
                        $productIds[] = $result['tire']['id'];
                    }
                }

                Log::info('Product IDs from search:', ['count' => count($productIds), 'ids' => $productIds]);

                // Query products theo IDs và filter is_active
                $productsById = Product::whereIn('id', $productIds)
                    ->where('is_active', true) // ✅ Filter is_active ở đây thay vì Meilisearch
                    ->with(['translations' => function ($query) use ($currentLocale) {
                        $query->where('language', $currentLocale);
                    }])
                    ->get()
                    ->keyBy('id'); // Key by ID để map lại theo thứ tự

                Log::info('Products queried from DB:', [
                    'count' => $productsById->count(),
                    'ids' => $productsById->keys()->toArray(),
                    'details' => $productsById->map(fn($p) => [
                        'id' => $p->id,
                        'sku' => $p->sku,
                        'is_active' => $p->is_active,
                        'name' => $p->translations->first()?->name ?? 'N/A'
                    ])
                ]);

                // Sắp xếp lại products theo thứ tự search results (relevance)
                $allProducts = collect($productIds)
                    ->map(function ($id) use ($productsById) {
                        return $productsById->get($id);
                    })
                    ->filter() // Remove null
                    ->map(function ($product) {
                        $translation = $product->translations->first();
                        $product->product_name = $translation ? $translation->name : $product->sku;
                        $product->product_description = $translation ? $translation->description : null;
                        $product->short_description = $translation ? $translation->short_description : null;

                        $product->slug = $translation ? $translation->slug : $product->id;
                        // ✅ Lấy category slug và name
                        $category = $product->getMainCategory();
                        if (!$category && $product->category_id) {
                            $category = $product->category;
                        }
                        if (!$category) {
                            $category = $product->categories->first();
                        }

                        if ($category) {
                            $categoryTranslation = $category->translations()->where('language', app()->getLocale())->first();
                            $product->category_slug = $categoryTranslation ? $categoryTranslation->slug : ($category->slug ?? $category->id);
                            $product->category_name = $categoryTranslation ? $categoryTranslation->name : ($category->code ?? 'Venturer');
                        }
                        $product->image = $this->getImageJson($product->image_urls);
                        $product->all_images = $this->getAllImagesJson($product->image_urls);
                        $product->has_display_price = $product->hasDisplayablePrice();
                        $product->price_display = $product->priceDisplayLabel();
                        return $product;
                    });
                Log::info('Final $allProducts to display:', [
                    'count' => $allProducts->count(),
                    'products' => $allProducts->map(fn($p) => [
                        'id' => $p->id,
                        'sku' => $p->sku,
                        'name' => $p->product_name,
                        'is_active' => $p->is_active
                    ])->toArray()
                ]);

                // ✅ Lấy category IDs từ các sản phẩm trong kết quả tìm kiếm
                $categoryIds = collect();
                foreach ($productsById as $product) {
                    // Lấy từ category_id (backward compatibility)
                    if ($product->category_id) {
                        $categoryIds->push($product->category_id);
                    }
                    // Lấy từ pivot table (many-to-many)
                    $product->load('categories');
                    foreach ($product->categories as $category) {
                        $categoryIds->push($category->id);
                    }
                }
                $categoryIds = $categoryIds->unique()->values()->toArray();

                // ✅ Chỉ lấy những categories có sản phẩm trong kết quả tìm kiếm
                $categories = ProductCategory::with(['translations' => function ($query) use ($currentLocale) {
                    $query->where('language', $currentLocale);
                }])
                    ->whereIn('id', $categoryIds)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($category) {
                        $translation = $category->translations->first();
                        $category->category_name = $translation ? $translation->name : null;
                        $category->category_description = $translation ? $translation->description : null;
                        return $category;
                    });
            } else {
                $allProducts = collect();
                $categories = collect();
            }
        } else {
            // Không có search → Hiển thị products mặc định
            $allProducts = Product::with(['translations' => function ($query) use ($currentLocale) {
                $query->where('language', $currentLocale);
            }])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(15)
                ->get()
                ->map(function ($product) {
                    $translation = $product->translations->first();
                    $product->product_name = $translation ? $translation->name : null;
                    $product->product_description = $translation ? $translation->description : null;
                    $product->short_description = $translation ? $translation->short_description : null;
                    $product->slug = $translation ? $translation->slug : null;
                    $product->image = $this->getImageJson($product->image_urls);
                    $product->all_images = $this->getAllImagesJson($product->image_urls);
                    $product->has_display_price = $product->hasDisplayablePrice();
                    $product->price_display = $product->priceDisplayLabel();
                    return $product;
                });

            // Lấy categories với translations (tất cả categories khi không có search)
            $categories = ProductCategory::with(['translations' => function ($query) use ($currentLocale) {
                $query->where('language', $currentLocale);
            }])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(20)
                ->get()
                ->map(function ($category) {
                    $translation = $category->translations->first();
                    $category->category_name = $translation ? $translation->name : null;
                    $category->category_description = $translation ? $translation->description : null;
                    return $category;
                });
        }

        // Nhóm sản phẩm theo danh mục
        foreach ($categories as $category) {
            // ✅ Nếu có search query, chỉ lấy sản phẩm trong kết quả tìm kiếm
            if (!empty($searchQuery) && !empty($productIds)) {
                $categoryProducts = Product::with(['translations' => function ($query) use ($currentLocale) {
                    $query->where('language', $currentLocale);
                }])
                    ->whereIn('id', $productIds)
                    ->where(function ($query) use ($category) {
                        // Kiểm tra category_id (backward compatibility)
                        $query->where('category_id', $category->id)
                            // Hoặc kiểm tra trong pivot table (many-to-many)
                            ->orWhereHas('categories', function ($q) use ($category) {
                                $q->where('product_categories.id', $category->id);
                            });
                    })
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(50)
                    ->get();
            } else {
                // Không có search → Lấy tất cả sản phẩm của category
                $categoryProducts = Product::with(['translations' => function ($query) use ($currentLocale) {
                    $query->where('language', $currentLocale);
                }])
                    ->where(function ($query) use ($category) {
                        // Kiểm tra category_id (backward compatibility)
                        $query->where('category_id', $category->id)
                            // Hoặc kiểm tra trong pivot table (many-to-many)
                            ->orWhereHas('categories', function ($q) use ($category) {
                                $q->where('product_categories.id', $category->id);
                            });
                    })
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(50)
                    ->get();
            }

            $categoryProducts = $categoryProducts->map(function ($product) {
                // Chuyển đổi dữ liệu từ relation sang thuộc tính trực tiếp
                $translation = $product->translations->first();
                $product->product_name = $translation ? $translation->name : null;
                $product->product_description = $translation ? $translation->description : null;
                $product->short_description = $translation ? $translation->short_description : null;
                $product->slug = $translation ? $translation->slug : null;

                // Xử lý hình ảnh
                $product->image = $this->getImageJson($product->image_urls);
                $product->all_images = $this->getAllImagesJson($product->image_urls);
                $product->has_display_price = $product->hasDisplayablePrice();
                $product->price_display = $product->priceDisplayLabel();

                return $product;
            });

            // Lưu danh sách sản phẩm đã được nhóm theo danh mục
            $category->products = $categoryProducts;
        }

        $categoryParentCode = $this->resolveCategorySearchBannerParentCode(
            $searchQuery,
            $allProducts ?? null,
            $categories ?? collect()
        );

        return view('langding.category-search', compact(
            'carSearchData',
            'categories',
            'allProducts',
            'searchQuery',
            'searchTotal',
            'categoryParentCode'
        ));
    }

    /**
     * Code cha: phần trước "_" đầu tiên (vd: 03_01 → 03, 01 → 01).
     */
    private function parentCategoryCode(?string $code): string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return '';
        }

        $parts = explode('_', $code, 2);

        return $parts[0];
    }

    /**
     * Banner: ưu tiên danh mục của sản phẩm đầu tiên trong kết quả search (đúng thứ tự relevance).
     */
    private function resolveCategorySearchBannerParentCode(
        ?string $searchQuery,
        $allProducts,
        $categories
    ): ?string {
        if (!empty($searchQuery) && $allProducts !== null && $allProducts->isNotEmpty()) {
            $first = $allProducts->first();
            $first->loadMissing('categories');
            $category = $first->getMainCategory();
            if (!$category && $first->category_id) {
                $category = $first->category;
            }
            if (!$category) {
                $category = $first->categories->first();
            }
            if ($category) {
                $parent = $this->parentCategoryCode($category->code ?? '');
                if ($parent !== '') {
                    return $parent;
                }
            }
        }

        if ($categories !== null && $categories->isNotEmpty()) {
            $firstCat = $categories->first();
            $parent = $this->parentCategoryCode($firstCat->code ?? '');
            if ($parent !== '') {
                return $parent;
            }
        }

        return null;
    }
}
