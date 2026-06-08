<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Traits\HasImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    use HasImage;

    /**
     * Lấy danh sách sản phẩm theo danh mục
     * 
     * @param ProductCategory $category
     * @param array $filters
     * @param string $sortBy
     * @param int $perPage
     * @param string $locale
     * @return array ['products' => Collection, 'pagination' => LengthAwarePaginator]
     */
    public function getProductsByCategory(
        ProductCategory $category,
        array $filters = [],
        string $sortBy = 'sort_order',
        int $perPage = 15,
        ?string $locale = null
    ): array {
        // Lấy locale từ app nếu không được truyền vào
        if ($locale === null) {
            $locale = app()->getLocale();
        }
        // Query products
        $query = Product::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->where(function ($q) use ($category) {
                // Hỗ trợ cả many-to-many và backward compatibility
                $q->whereHas('categories', function ($subQ) use ($category) {
                    $subQ->where('product_categories.id', $category->id);
                })->orWhere('category_id', $category->id);
            });

        // Apply filters
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->featured();
        }

        if (isset($filters['is_bestseller']) && $filters['is_bestseller']) {
            $query->bestseller();
        }

        if (isset($filters['is_new']) && $filters['is_new']) {
            $query->new();
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        $this->applySorting($query, $sortBy, $category, $locale);

        // Pagination
        $paginated = $query->paginate($perPage);

        // Format products
        $products = $paginated->getCollection()->map(function ($product) use ($locale) {
            return $this->formatProduct($product, $locale);
        });

        return [
            'products' => $products,
            'pagination' => $paginated
        ];
    }

    /**
     * Áp dụng sorting cho query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param ProductCategory $category
     * @param string $locale
     * @return void
     */
    private function applySorting($query, string $sortBy, ProductCategory $category, string $locale): void
    {
        switch ($sortBy) {
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price':
                $query->orderByPrice('asc');
                break;
            case 'price_desc':
                $query->orderByPrice('desc');
                break;
            case 'name':
                // Dùng subquery thay vì join để tránh conflict với with()
                // Sử dụng parameter binding để tránh SQL injection
                $query->orderByRaw("(
                    SELECT name 
                    FROM product_translations 
                    WHERE product_translations.product_id = products.id 
                    AND product_translations.language = ?
                    LIMIT 1
                ) ASC", [$locale]);
                break;
            case 'sort_order':
            default:
                // Sắp xếp theo pivot sort_order nếu có, nếu không thì theo product sort_order
                // Dùng subquery thay vì leftJoin để tránh conflict với with()
                // Sử dụng parameter binding để tránh SQL injection
                $query->orderByRaw("(
                    SELECT sort_order 
                    FROM product_product_category 
                    WHERE product_product_category.product_id = products.id 
                    AND product_product_category.product_category_id = ?
                    LIMIT 1
                ) ASC", [$category->id])
                    ->orderBy('products.sort_order', 'asc');
                break;
        }
    }

    /**
     * Lấy sản phẩm bán chạy
     * 
     * @param int $limit
     * @param string|null $locale
     * @return Collection
     */
    public function getBestsellerProducts(int $limit = 10, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $fallbackLocale = $this->resolveAlternateLocale($locale);

        return Product::query()
            ->where('is_active', true)
            ->where('is_bestseller', true)
            ->with(['documentFile', 'translations' => function ($q) use ($locale, $fallbackLocale) {
                $q->whereIn('language', [$locale, $fallbackLocale]);
            }])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(function ($product) use ($locale) {
                return $this->formatProductForFrontend($product, $locale);
            });
    }

    /**
     * Lấy tất cả sản phẩm (có limit)
     * 
     * @param int $limit
     * @param string|null $locale
     * @return Collection
     */
    public function getAllProducts(int $limit = 15, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return Product::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(function ($product) use ($locale) {
                return $this->formatProductForFrontend($product, $locale);
            });
    }

    /**
     * Lấy sản phẩm theo category ID (dùng cho frontend)
     * 
     * @param int $categoryId
     * @param string|null $locale
     * @return Collection
     */
    public function getProductsByCategoryId(int $categoryId, ?string $locale = null, int $limit = 50): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return Product::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->where(function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($subQ) use ($categoryId) {
                    $subQ->where('product_categories.id', $categoryId);
                })->orWhere('category_id', $categoryId);
            })
            ->orderBy('sort_order')
            ->limit(50)
            ->get()
            ->map(function ($product) use ($locale) {
                return $this->formatProductForFrontend($product, $locale);
            });
    }

    /**
     * Format product data cho API response
     * 
     * @param Product $product
     * @param string $locale
     * @return array
     */
    private function formatProduct(Product $product, string $locale): array
    {
        $translation = $product->translations->firstWhere('language', $locale);
        $effectiveImageUrls = $this->resolveEffectiveImageUrls($translation, $product, $locale);

        $hasDisplay = $product->hasDisplayablePrice();
        $hasSale = $product->hasValidSalePromotion();

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $translation->name ?? 'N/A',
            'slug' => $translation->slug ?? null,
            'description' => $translation->description ?? null,
            'short_description' => $translation->short_description ?? null,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'has_display_price' => $hasDisplay,
            'has_sale_promotion' => $hasSale,
            'price_display' => $product->priceDisplayLabel(),
            'formatted_price' => $hasDisplay
                ? number_format((float) $product->price, 0, ',', '.') . ' đ'
                : __('messages.contact'),
            'formatted_sale_price' => $hasSale
                ? number_format((float) $product->sale_price, 0, ',', '.') . ' đ'
                : null,
            'stock_quantity' => $product->stock_quantity,
            'is_featured' => $product->is_featured,
            'is_bestseller' => $product->is_bestseller,
            'is_new' => $product->is_new,
            'image' => $this->getImageJson($effectiveImageUrls),
            'all_images' => $this->getAllImagesJson($effectiveImageUrls),
            'created_at' => $product->created_at?->toISOString(),
            'updated_at' => $product->updated_at?->toISOString(),
        ];
    }

    /**
     * Lấy sản phẩm theo slug hoặc ID (dùng cho frontend)
     * 
     * @param string|int $identifier Slug hoặc ID
     * @param string|null $locale
     * @return Product|null
     */
    public function getProductBySlugOrId($identifier, ?string $locale = null): ?Product
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $query = Product::query()
            ->where('is_active', true)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }]);

        if (is_numeric($identifier)) {
            $product = $query->find($identifier);
        } else {
            $product = $query->whereHas('translations', function ($q) use ($identifier, $locale) {
                $q->where('slug', $identifier)->where('language', $locale);
            })->first();
        }

        if ($product) {
            return $this->formatProductForFrontend($product, $locale);
        }

        return null;
    }

    /**
     * Lấy sản phẩm liên quan theo category
     * 
     * @param int $categoryId
     * @param int $excludeProductId
     * @param int $limit
     * @param string|null $locale
     * @return Collection
     */
    public function getRelatedProducts(int $categoryId, int $excludeProductId, int $limit = 6, ?string $locale = null): Collection
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        return Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $excludeProductId)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->where(function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($subQ) use ($categoryId) {
                    $subQ->where('product_categories.id', $categoryId);
                })->orWhere('category_id', $categoryId);
            })
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(function ($product) use ($locale) {
                return $this->formatProductForFrontend($product, $locale);
            });
    }

    /**
     * Lấy tất cả sản phẩm với filters và pagination
     * 
     * @param array $filters
     * @param string $sortBy
     * @param int $perPage
     * @param string|null $locale
     * @return array
     */
    public function getAllProductsWithFilters(
        array $filters = [],
        string $sortBy = 'sort_order',
        int $perPage = 15,
        ?string $locale = null
    ): array {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $query = Product::query()
            ->where('is_active', true)
            ->with([
                'translations' => function ($q) use ($locale) {
                    $q->where('language', $locale);
                },
                // ✅ THÊM PHẦN NÀY - Load category với translations
                'categories' => function ($q) use ($locale) {
                    $q->where('is_active', true)
                        ->with(['translations' => function ($tq) use ($locale) {
                            $tq->where('language', $locale);
                        }]);
                }
            ]);

        // Apply filters
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->where('is_featured', true);
        }

        if (isset($filters['is_bestseller']) && $filters['is_bestseller']) {
            $query->where('is_bestseller', true);
        }

        if (isset($filters['is_new']) && $filters['is_new']) {
            $query->where('is_new', true);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $categoryIds = $filters['category_ids'];
            $query->where(function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds)
                    ->orWhereHas('categories', function ($subQ) use ($categoryIds) {
                        $subQ->whereIn('product_categories.id', $categoryIds);
                    });
            });
        }

        if (!empty($filters['attribute_value_ids']) && is_array($filters['attribute_value_ids'])) {
            $attributeValueIds = array_values(array_filter($filters['attribute_value_ids']));
            if (count($attributeValueIds) > 0) {
                $query->whereHas('attributeValues', function ($q) use ($attributeValueIds) {
                    $q->whereIn('product_attribute_values.id', $attributeValueIds);
                });
            }
        }


        // Search in SKU or translations
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search, $locale) {
                $q->where('sku', 'LIKE', "%{$search}%")
                    ->orWhereHas('translations', function ($tq) use ($search, $locale) {
                        $tq->where('language', $locale)
                            ->where(function ($nameQuery) use ($search) {
                                $nameQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('description', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        // Apply sorting - ✅ Always prioritize: featured → new → bestseller → then sort_by criteria
        $query->orderBy('is_featured', 'DESC')
            ->orderBy('is_new', 'DESC')
            ->orderBy('is_bestseller', 'DESC');

        // Then apply secondary sort based on sortBy parameter
        switch ($sortBy) {
            case 'created_at':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'price':
                $query->orderBy('price', 'ASC');
                break;
            case 'price_desc':
                $query->orderBy('price', 'DESC');
                break;
            case 'name':
                $query->leftJoin('product_translations', function ($join) use ($locale) {
                    $join->on('products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language', '=', $locale);
                })
                    ->orderBy('product_translations.name', 'ASC')
                    ->select('products.*');
                break;
            case 'sort_order':
            default:
                $query->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC');
                break;
        }

        // Paginate
        $paginated = $query->paginate($perPage)->onEachSide(1)->withQueryString();
        // Format products
        $products = $paginated->getCollection()->map(function ($product) use ($locale) {
            return $this->formatProductForFrontend($product, $locale);
        });
        return [
            'products' => $products,
            'pagination' => $paginated
        ];
    }

    /**
     * Format product data cho frontend (Blade views)
     * 
     * @param Product $product
     * @param string|null $locale
     * @return object
     */
    public function formatProductForFrontend(Product $product, ?string $locale = null): object
    {
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        $translation = $product->translations->firstWhere('language', $locale);
        $effectiveImageUrls = $this->resolveEffectiveImageUrls($translation, $product, $locale);

        if ($product->is_bestseller) {
            // Sản phẩm bán chạy: không lấy ảnh đầu tiên
            $product->image = null;

            // Lấy all_images nhưng bỏ ảnh đầu tiên
            $allImages = $this->getAllImagesJson($effectiveImageUrls);
            if (count($allImages) > 0) {
                array_shift($allImages); // Bỏ ảnh đầu tiên
            }
            $product->all_images2 = $allImages;
        } else {
            // Sản phẩm thường
            $product->all_images2 = $this->getAllImagesJson($effectiveImageUrls);
        }

        $product->product_name = $translation?->name ?? null;
        $product->product_description = $translation?->description ?? null;
        $product->short_description = $translation?->short_description ?? null;
        $product->slug = $translation?->slug ?? null;
        $product->image = $this->getImageJson($effectiveImageUrls);
        $product->all_images = $this->getAllImagesJson($effectiveImageUrls);




        // ✅ Thêm features và specifications
        $product->features = $translation?->features ?? [];
        $product->specifications = $translation?->specifications ?? [];
        $product->outstanding_features = $translation?->outstanding_features ?? null;


        if (!is_array($product->specifications)) {
            $product->specifications = !empty($product->specifications) ? json_decode($product->specifications, true) ?? [] : [];
        }

        $product->has_display_price = $product->hasDisplayablePrice();
        $product->has_sale_promotion = $product->hasValidSalePromotion();
        $product->price_display = $product->priceDisplayLabel();

        if (!$product->relationLoaded('documentFile')) {
            $product->load('documentFile');
        }

        $product->pdf_url = ($product->document_file_id && $product->documentFile)
            ? route('product.document', ['slug' => $product->slug ?? $product->id])
            : null;

        // ✅ Lấy specifications từ attributes có show_detail = 'Y'
        $specificationsFromAttributes = $product->getSpecificationsFromAttributes($locale);

        // ✅ Merge với specifications từ translation (ưu tiên translation nếu trùng key)
        if (!empty($specificationsFromAttributes)) {
            if (is_array($product->specifications) && !empty($product->specifications)) {
                // Merge: attributes trước, translation sau (translation sẽ override nếu trùng key)
                $product->specifications = array_merge($specificationsFromAttributes, $product->specifications);
            } else {
                $product->specifications = $specificationsFromAttributes;
            }
        }

        return $product;
    }

    /**
     * Ưu tiên image_urls từ bản dịch (product_translations), không có thì dùng products.image_urls.
     *
     * @param  \App\Models\ProductTranslation|null  $translation
     * @return array<int, string>
     */
    private function resolveEffectiveImageUrls($translation, Product $product, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $fallbackLocale = $this->resolveAlternateLocale($locale);

        if (!$product->relationLoaded('translations')) {
            $product->load(['translations' => function ($q) use ($locale, $fallbackLocale) {
                $q->whereIn('language', [$locale, $fallbackLocale]);
            }]);
        }

        $fallbackTranslation = $product->translations->firstWhere('language', $fallbackLocale);

        $candidates = [
            $this->normalizeImageUrlList($translation?->image_urls ?? null),
            $this->normalizeImageUrlList($fallbackTranslation?->image_urls ?? null),
            $this->normalizeImageUrlList($product->image_urls),
        ];

        foreach ($candidates as $urls) {
            if ($this->imageUrlListHasResolvableFile($urls)) {
                return $urls;
            }
        }

        return [];
    }

    /**
     * @param  mixed  $imageUrls
     * @return array<int, string>
     */
    private function normalizeImageUrlList($imageUrls): array
    {
        if ($imageUrls === null || $imageUrls === '') {
            return [];
        }

        if (is_string($imageUrls)) {
            $decoded = json_decode($imageUrls, true);
            $imageUrls = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($imageUrls)) {
            return [];
        }

        return array_values(array_filter($imageUrls, static function ($url) {
            return $url !== null && $url !== '';
        }));
    }

    private function resolveAlternateLocale(string $currentLocale): string
    {
        foreach (array_keys(config('languages.supported', ['en' => 'English', 'vi' => 'Tiếng Việt'])) as $code) {
            if ($code !== $currentLocale) {
                return $code;
            }
        }

        return $currentLocale === 'vi' ? 'en' : 'vi';
    }
}
