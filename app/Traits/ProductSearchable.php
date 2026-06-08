<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Trait ProductSearchable
 * 
 * Xử lý tất cả search logic cho products:
 * - Text search (Meilisearch - fuzzy)
 * - Voice search (Meilisearch - fuzzy)
 * - Dropdown search (Database - exact match)
 * 
 * @package App\Traits
 */
trait ProductSearchable
{
    /**
     * ✅ UNIFIED SEARCH - Xử lý tất cả loại search
     * 
     * @param string $query User's search query
     * @param string $searchType 'text', 'voice', 'car', 'size'
     * @param float $confidence Voice recognition confidence score (0.0 - 1.0)
     * @return array Formatted search results
     */
    // Dòng 29 - Sửa signature
    public function performSearch(string $query, string $searchType = 'text', float $confidence = 1.0, string $vehicleType = 'oto'): array
    {
        try {
            Log::info('PRODUCT_SEARCH_REQUEST', [
                'query' => $query,
                'type' => $searchType,
                'confidence' => $confidence,
                'vehicleType' => $vehicleType, // ✅ Thêm vào log
                'locale' => app()->getLocale()
            ]);

            // ✅ PHÂN BIỆT: Dropdown search vs Text/Voice search
            if ($searchType === 'car' || $searchType === 'size') {
                // ✅ DROPDOWN → DATABASE (exact match, NO Meilisearch)
                Log::info('🎯 USING DATABASE EXACT SEARCH', [
                    'type' => $searchType,
                    'reason' => 'Dropdown search requires 100% accuracy'
                ]);
                return $this->searchByExactAttributes($query, $searchType);
            } else {
                // ❌ TEXT/VOICE → MEILISEARCH (fuzzy search) với fallback
                Log::info('🔍 USING MEILISEARCH', [
                    'type' => $searchType,
                    'reason' => 'Text/Voice search allows fuzzy matching'
                ]);
                $cleanQuery = $this->extractSearchKeywords($query, $searchType);

                // ✅ FIX: Thử Meilisearch trước, nếu không có kết quả thì fallback về database
                try {
                    $results = $this->searchByMeilisearch($cleanQuery);

                    // ✅ Nếu Meilisearch không có kết quả, fallback về database
                    if (empty($results)) {
                        Log::info('MEILISEARCH_NO_RESULTS_FALLBACK_TO_DB', [
                            'query' => $cleanQuery
                        ]);
                        return $this->searchByDatabaseFallback($cleanQuery, $vehicleType);
                    }

                    return $results;
                } catch (\Exception $e) {
                    Log::warning('MEILISEARCH_FAILED_FALLBACK_TO_DB', [
                        'query' => $cleanQuery,
                        'error' => $e->getMessage()
                    ]);

                    // ✅ Fallback: Tìm kiếm trong database với text_search
                    return $this->searchByDatabaseFallback($cleanQuery, $vehicleType);
                }
            }
        } catch (Exception $e) {
            Log::error('PRODUCT_SEARCH_ERROR', [
                'query' => $query,
                'type' => $searchType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * 🔍 MEILISEARCH - Fuzzy search cho text/voice
     * Hỗ trợ: typo tolerance, relevance ranking, fast search
     * 
     * @param string $query Clean search keywords
     * @return array Formatted product results
     */
    protected function searchByMeilisearch(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            Log::info('MEILISEARCH_QUERY', ['query' => $query]);

            // Search với Laravel Scout + Meilisearch
            $products = Product::search($query)
                // ⚠️ TEMPORARY: Bỏ filter is_active vì chưa setup filterableAttributes
                // ->where('is_active', true)
                ->take(100)
                ->get();

            // ⚠️ IMPORTANT: Laravel Scout KHÔNG tự động eager load relationships!
            // Phải load manually để tránh N+1 query
            $products->load([
                'translations',
                'attributeValues.attribute',
                'attributeValues.translations'
            ]);

            Log::info('MEILISEARCH_RAW_RESULTS', [
                'query' => $query,
                'total' => $products->count(),
                'products' => $products->map(fn($p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->translations->first()?->name ?? 'N/A'
                ])->toArray()
            ]);

            // ✅ Filter products based on keyword relevance
            $filteredProducts = $this->filterByKeywordRelevance($products, $query);

            Log::info('MEILISEARCH_FILTERED_RESULTS', [
                'query' => $query,
                'before_filter' => $products->count(),
                'after_filter' => $filteredProducts->count()
            ]);

            return $this->formatSearchResults($filteredProducts);
        } catch (Exception $e) {
            Log::error('MEILISEARCH_SEARCH_ERROR', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 🎯 DATABASE EXACT SEARCH - Cho dropdown
     * Query database trực tiếp để đảm bảo 100% chính xác
     * 
     * @param string $query Structured query từ dropdown (e.g., "Thương hiệu: HONDA, Mẫu xe: BRIO")
     * @param string $searchType 'car' or 'size'
     * @return array Formatted product results
     */
    protected function searchByExactAttributes(string $query, string $searchType): array
    {
        try {
            // Parse structured query thành attribute array
            $attributes = $this->parseStructuredQuery($query, $searchType);

            Log::info('EXACT_SEARCH_ATTRIBUTES', [
                'query' => $query,
                'type' => $searchType,
                'parsed_attributes' => $attributes
            ]);

            if (empty($attributes)) {
                Log::warning('EXACT_SEARCH_NO_ATTRIBUTES', ['query' => $query]);
                return [];
            }

            // Tìm theo mẫu xe (Thương hiệu + Mẫu xe + Năm): dùng bảng fitments, vì dropdown lấy từ fitments
            $products = $this->queryProductsForExactSearch($attributes, $searchType);

            Log::info('EXACT_SEARCH_RESULTS', [
                'query' => $query,
                'total' => $products->count(),
                'products' => $products->map(fn($p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->translations->first()?->name ?? 'N/A'
                ])->toArray()
            ]);

            // ✅ KHÔNG filter by keyword relevance cho dropdown search
            // Dropdown search đã là exact match rồi
            return $this->formatSearchResults($products);
        } catch (Exception $e) {
            Log::error('EXACT_SEARCH_ERROR', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Parse structured query → attribute array
     * 
     * Examples:
     * - "Thương hiệu: HONDA, Mẫu xe: BRIO, Năm: 2019" 
     *   → ['manufacturer' => 'HONDA', 'model' => 'BRIO', 'production_year' => '2019']
     * - "Độ rộng: 185, Tỷ lệ: 65, Đường kính: 15"
     *   → ['wide' => '185', 'rate' => '65', 'diameter' => '15']
     * 
     * @param string $query
     * @param string $searchType
     * @return array Attribute code => value mapping
     */
    protected function parseStructuredQuery(string $query, string $searchType): array
    {
        $attributes = [];
        // Với truy vấn theo quy cách, giá trị có thể chứa dấu phẩy thập phân (vd "22,5").
        // Nếu split theo ',' sẽ làm vỡ token. Chuẩn hoá "22,5" → "22.5" trước khi tách.
        if ($searchType === 'size') {
            $query = preg_replace('/(\d),(\d)/', '$1.$2', $query);
        }
        $parts = explode(',', $query);

        foreach ($parts as $part) {
            if (strpos($part, ':') === false) {
                continue;
            }

            [$label, $value] = explode(':', $part, 2);
            $label = trim($label);
            $value = trim($value);

            if (empty($value)) {
                continue;
            }

            // Map labels → attribute codes
            if ($searchType === 'car') {
                if (stripos($label, 'Thương hiệu') !== false || stripos($label, 'Brand') !== false) {
                    $attributes['manufacturer'] = $value;
                } elseif (stripos($label, 'Mẫu xe') !== false || stripos($label, 'Model') !== false) {
                    $attributes['model'] = $value;
                } elseif (stripos($label, 'Năm') !== false || stripos($label, 'Year') !== false) {
                    // ✅ Không thêm vào attributes nếu giá trị là "Tất cả"
                    if (mb_strtolower(trim($value)) !== 'tất cả' && mb_strtolower(trim($value)) !== 'All') {
                        $attributes['production_year'] = $value;
                    }
                }
            } elseif ($searchType === 'size') {
                if (stripos($label, 'Độ rộng') !== false || stripos($label, 'Width') !== false) {
                    $attributes['wide'] = $value;
                } elseif (stripos($label, 'Tỷ lệ') !== false || stripos($label, 'Ratio') !== false) {
                    $attributes['rate'] = $value;
                } elseif (stripos($label, 'Đường kính') !== false || stripos($label, 'Diameter') !== false) {
                    // Hỗ trợ dấu thập phân kiểu VN (22,5) và kiểu EN (22.5)
                    $attributes['diameter'] = str_replace(',', '.', $value);
                }
            }
        }

        return $attributes;
    }

    /**
     * Chọn nguồn query cho exact search: car → fitments (nếu có manufacturer/model), size/car còn lại → attributes.
     *
     * @param array  $attributes
     * @param string $searchType  'car' | 'size'
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function queryProductsForExactSearch(array $attributes, string $searchType)
    {
        if ($searchType === 'car') {
            $manufacturer = $attributes['manufacturer'] ?? null;
            $model = $attributes['model'] ?? null;
            $year = $attributes['production_year'] ?? null;
            if ($manufacturer !== null && $model !== null) {
                $byFitment = $this->queryProductsByFitment($manufacturer, $model, $year);
                if ($byFitment->isNotEmpty()) {
                    return $byFitment;
                }
            }
        }
        return $this->queryProductsByAttributes($attributes);
    }

    /**
     * Query sản phẩm theo vehicle fitment (manufacturer, model, year).
     * Dropdown mẫu xe lấy từ fitments nên tìm theo fitments mới ra kết quả.
     *
     * @param string      $manufacturer
     * @param string      $model
     * @param string|null $year
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function queryProductsByFitment(string $manufacturer, string $model, ?string $year = null)
    {
        $manufacturer = trim($manufacturer);
        $model = trim($model);
        if ($manufacturer === '' || $model === '') {
            return collect();
        }

        $query = Product::query()
            ->where('is_active', true)
            ->whereHas('vehicleFitments', function ($q) use ($manufacturer, $model, $year) {
                $q->where('manufacturer', $manufacturer)->where('model', $model);
                if ($year !== null && trim($year) !== '') {
                    $q->where('year', trim($year));
                }
            });

        Log::info('QUERY_BY_FITMENT', [
            'manufacturer' => $manufacturer,
            'model' => $model,
            'year' => $year,
        ]);

        $results = $query->with([
            'translations',
            'attributeValues.attribute',
            'attributeValues.translations',
        ])->get();

        Log::info('QUERY_BY_FITMENT_RESULT', [
            'total' => $results->count(),
            'product_ids' => $results->pluck('id')->toArray(),
        ]);

        return $results;
    }

    /**
     * Query products có TẤT CẢ attributes trong array
     * 
     * @param array $attributes ['manufacturer' => 'HONDA', 'model' => 'BRIO', ...]
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function queryProductsByAttributes(array $attributes)
    {
        Log::info('QUERY_BY_ATTRIBUTES_START', ['attributes' => $attributes]);

        // ⚠️ TEMPORARY: Bỏ filter is_active cho dropdown search
        $query = Product::query();
        // ->where('is_active', true);

        // Với mỗi attribute, filter products phải có attribute đó
        foreach ($attributes as $attributeCode => $attributeValue) {
            Log::info('ADDING_ATTRIBUTE_FILTER', [
                'code' => $attributeCode,
                'value' => $attributeValue
            ]);

            $query->whereHas('attributeValues', function ($q) use ($attributeCode, $attributeValue) {
                $q->whereHas('attribute', function ($attrQ) use ($attributeCode) {
                    $attrQ->where('code', $attributeCode);
                })
                    ->whereHas('translations', function ($transQ) use ($attributeValue) {
                        // ✅ EXACT MATCH cho dropdown search (case-insensitive)
                        // Tránh trường hợp "MORNING" match với "MAZDA2 MORNING EDITION"
                        $needle = mb_strtolower(trim((string) $attributeValue));
                        $needleNormalized = str_replace(',', '.', $needle);

                        // Với giá trị có thể có dấu phẩy thập phân (vd 22,5), so sánh theo dạng chuẩn hoá.
                        // Điều này giúp match được cả DB lưu "22,5" lẫn "22.5".
                        $transQ->whereRaw("REPLACE(LOWER(value), ',', '.') = ?", [$needleNormalized]);
                    });
            });
        }

        // Debug SQL query
        $sql = $query->toSql();
        Log::info('QUERY_SQL', ['sql' => $sql]);

        // Eager load relationships để tránh N+1
        $results = $query->with([
            'translations',
            'attributeValues.attribute',
            'attributeValues.translations'
        ])->get();

        Log::info('QUERY_BY_ATTRIBUTES_RESULT', [
            'total' => $results->count(),
            'product_ids' => $results->pluck('id')->toArray()
        ]);

        return $results;
    }

    /**
     * Extract keywords từ structured query
     * 
     * Examples:
     * - "Thương hiệu: HONDA, Mẫu xe: BRIO, Năm: 2019" → "HONDA BRIO 2019"
     * - "Độ rộng: 185, Tỷ lệ: 65, Đường kính: 15" → "185 65 15"
     * - "honda civic" → "honda civic" (unchanged)
     * 
     * @param string $query
     * @param string $searchType
     * @return string Clean keywords for Meilisearch
     */
    protected function extractSearchKeywords(string $query, string $searchType): string
    {
        // If it's regular text/voice search without structure, return as-is
        if (($searchType === 'text' || $searchType === 'voice') && strpos($query, ':') === false) {
            return $query;
        }

        // Extract values after colons
        $keywords = [];
        $parts = explode(',', $query);

        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                $segments = explode(':', $part, 2);
                if (isset($segments[1])) {
                    $value = trim($segments[1]);
                    if (!empty($value)) {
                        $keywords[] = $value;
                    }
                }
            } else {
                // No colon, just add the part
                $cleaned = trim($part);
                if (!empty($cleaned)) {
                    $keywords[] = $cleaned;
                }
            }
        }

        $cleanQuery = implode(' ', $keywords);

        return !empty($cleanQuery) ? $cleanQuery : $query;
    }

    /**
     * Format kết quả search thành format chuẩn cho frontend
     * 
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @return array
     */
    protected function formatSearchResults($products): array
    {
        return $products->map(function ($product) {
            // Get first image from image_urls array
            $imageUrls = $product->image_urls ?? [];
            $image = '/langding/imgs/product.png'; // Default image

            if (!empty($imageUrls)) {
                $firstImage = $imageUrls[0];
                // ✅ Ensure full path for image
                if (filter_var($firstImage, FILTER_VALIDATE_URL)) {
                    // Already a full URL
                    $image = $firstImage;
                } elseif (str_starts_with($firstImage, '/')) {
                    // Already starts with /
                    $image = $firstImage;
                } else {
                    // Just filename - add storage path
                    $image = '/storage/images/' . $firstImage;
                }
            }

            // Get product name and description from translation
            $translation = $product->translations->where('language', app()->getLocale())->first();
            $productName = $translation?->name ?? $product->sku;
            $productDesc = $translation?->description ?? 'Lốp xe cao cấp';

            return [
                'score' => 1.0,
                'tire' => [
                    'id' => $product->id,
                    'name' => $productName,
                    'sku' => $product->sku,
                    'price' => $product->sale_price ?? $product->price,
                    'description' => strip_tags($productDesc),
                    'image' => $image,
                    'manufacturer' => $this->getAttributeValue($product, 'manufacturer'),
                    'model' => $this->getAttributeValue($product, 'model'),
                    'size' => $this->getAttributeValue($product, 'size'),
                    'production_year' => $this->getAttributeValue($product, 'production_year') ?? date('Y'),
                    'warranty' => $this->getAttributeValue($product, 'warranty'),
                    'production_type' => 'sedan'
                ]
            ];
        })->toArray();
    }

    /**
     * Get attribute value từ product
     * 
     * @param Product $product
     * @param string $attributeCode
     * @return string|null
     */
    protected function getAttributeValue($product, string $attributeCode): ?string
    {
        // Default values nếu không tìm thấy
        $defaults = [
            'manufacturer' => 'CASUMINA',
            'model' => 'STANDARD',
            'size' => '205/55R16'
        ];

        if (!$product->attributeValues) {
            return $defaults[$attributeCode] ?? null;
        }

        foreach ($product->attributeValues as $attrValue) {
            if (!$attrValue->attribute) {
                continue;
            }

            $attrCode = $attrValue->attribute->code ?? '';

            // Check if this is the attribute we're looking for
            if ($attrCode === $attributeCode) {
                $translation = $attrValue->translations
                    ->where('language', app()->getLocale())
                    ->first();

                return $translation?->value ?? $defaults[$attributeCode] ?? null;
            }
        }

        return $defaults[$attributeCode] ?? null;
    }

    /**
     * Filter products dựa trên keyword relevance
     * Chỉ giữ lại products match ít nhất 70% keywords trong query
     * 
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function filterByKeywordRelevance($products, $query)
    {
        // Parse query thành keywords (lowercase, remove special chars)
        $queryKeywords = $this->parseQueryKeywords($query);

        if (count($queryKeywords) === 0) {
            return $products;
        }

        Log::info('FILTER_KEYWORDS', ['query' => $query, 'keywords' => $queryKeywords]);

        return $products->filter(function ($product) use ($queryKeywords) {
            $translation = $product->translations->where('language', app()->getLocale())->first();
            $textSearch = $translation?->text_search ?? '';

            // Nếu không có text_search, fallback về name + sku
            if (empty($textSearch)) {
                $textSearch = mb_strtolower($translation?->name ?? '', 'UTF-8') . ' ' . mb_strtolower($product->sku, 'UTF-8');
            } else {
                // ✅ FIX: Convert text_search về lowercase để so sánh với keywords (đã lowercase)
                $textSearch = mb_strtolower($textSearch, 'UTF-8');
            }

            // Đếm bao nhiêu keywords match
            $matchCount = 0;
            foreach ($queryKeywords as $keyword) {
                if (mb_strpos($textSearch, $keyword) !== false) {
                    $matchCount++;
                }
            }

            // Calculate match ratio
            $matchRatio = $matchCount / count($queryKeywords);

            // ✅ Threshold khác nhau cho từ vs số
            // Size search (toàn số): 50% (match 2/3 số cũng OK)
            // Text search (từ): 70% (cần match chặt chẽ hơn)
            $isNumericQuery = count(array_filter($queryKeywords, 'is_numeric')) / count($queryKeywords) >= 0.7;
            $threshold = $isNumericQuery ? 0.5 : 0.7;

            $shouldKeep = $matchRatio >= $threshold;

            Log::info('PRODUCT_RELEVANCE', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $translation?->name ?? 'N/A',
                'query_keywords' => $queryKeywords,
                'text_search_sample' => mb_substr($textSearch, 0, 100), // ✅ Thêm để debug
                'is_numeric_query' => $isNumericQuery,
                'threshold' => ($threshold * 100) . '%',
                'match_count' => $matchCount,
                'total_keywords' => count($queryKeywords),
                'match_ratio' => round($matchRatio * 100, 2) . '%',
                'keep' => $shouldKeep
            ]);

            return $shouldKeep;
        });
    }

    /**
     * Parse query thành array of keywords
     * 
     * @param string $query
     * @return array
     */
    protected function parseQueryKeywords($query)
    {
        // Lowercase
        $query = mb_strtolower($query, 'UTF-8');

        // Remove special characters nhưng giữ lại số và dấu /
        $query = preg_replace('/[^\p{L}\p{N}\s\/]/u', ' ', $query);

        // Split by whitespace
        $keywords = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        // Remove keywords quá ngắn (< 2 chars) trừ khi là số
        $keywords = array_filter($keywords, function ($keyword) {
            return mb_strlen($keyword, 'UTF-8') >= 2 || is_numeric($keyword);
        });

        return array_values($keywords);
    }
    /**
     * 🔄 DATABASE FALLBACK - Search trong cột text_search của product_translations
     * Khi Meilisearch không khả dụng hoặc không tìm thấy kết quả
     * 
     * @param string $query
     * @param string $vehicleType
     * @return array
     */
    protected function searchByDatabaseFallback(string $query, string $vehicleType = 'oto'): array
    {
        try {
            Log::info('DATABASE_FALLBACK_SEARCH', [
                'query' => $query,
                'vehicleType' => $vehicleType,
                'locale' => app()->getLocale()
            ]);

            // ✅ Parse query thành keywords (lowercase)
            $keywords = $this->parseQueryKeywords($query);

            if (empty($keywords)) {
                Log::warning('DATABASE_FALLBACK_NO_KEYWORDS', ['query' => $query]);
                return [];
            }

            $currentLocale = app()->getLocale();

            // ✅ Query products từ database - search trong text_search
            $queryBuilder = \App\Models\Product::query()
                ->where('is_active', true)
                ->whereHas('translations', function ($q) use ($keywords, $currentLocale) {
                    // ✅ FIX: Đặt language condition ra ngoài foreach
                    $q->where('language', $currentLocale)
                        ->where(function ($subQ) use ($keywords) {
                            // ✅ Tất cả keywords phải có trong text_search (AND logic)
                            foreach ($keywords as $keyword) {
                                $subQ->where('text_search', 'LIKE', "%{$keyword}%");
                            }
                        });
                });

            // ✅ Filter theo vehicleType nếu cần
            if ($vehicleType && $vehicleType !== 'oto') {
                $categoryId = $this->getCategoryIdByVehicleType($vehicleType);
                if ($categoryId) {
                    $queryBuilder->where(function ($q) use ($categoryId) {
                        $q->where('category_id', $categoryId)
                            ->orWhereHas('categories', function ($subQ) use ($categoryId) {
                                $subQ->where('product_categories.id', $categoryId);
                            });
                    });

                    Log::info('DATABASE_FALLBACK_FILTER_BY_VEHICLE', [
                        'vehicleType' => $vehicleType,
                        'categoryId' => $categoryId
                    ]);
                }
            }

            // ✅ Log SQL query để debug
            $sql = $queryBuilder->toSql();
            $bindings = $queryBuilder->getBindings();
            Log::info('DATABASE_FALLBACK_SQL', [
                'sql' => $sql,
                'bindings' => $bindings
            ]);

            // Eager load relationships để tránh N+1
            $products = $queryBuilder
                ->with([
                    'translations',
                    'attributeValues.attribute',
                    'attributeValues.translations'
                ])
                ->take(100)
                ->get();

            Log::info('DATABASE_FALLBACK_RESULTS', [
                'query' => $query,
                'keywords' => $keywords,
                'total' => $products->count(),
                'sample_products' => $products->take(5)->map(fn($p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'text_search' => $p->translations->where('language', $currentLocale)->first()?->text_search ?? ''
                ])->toArray()
            ]);

            // ✅ Nếu không có kết quả, thử search với OR logic (ít nhất 1 keyword match)
            if ($products->count() === 0 && count($keywords) > 1) {
                Log::info('DATABASE_FALLBACK_TRY_OR_LOGIC', [
                    'query' => $query,
                    'keywords' => $keywords
                ]);

                $queryBuilderOr = \App\Models\Product::query()
                    ->where('is_active', true)
                    ->whereHas('translations', function ($q) use ($keywords, $currentLocale) {
                        $q->where('language', $currentLocale)
                            ->where(function ($subQ) use ($keywords) {
                                // ✅ OR logic: ít nhất 1 keyword match
                                foreach ($keywords as $index => $keyword) {
                                    if ($index === 0) {
                                        $subQ->where('text_search', 'LIKE', "%{$keyword}%");
                                    } else {
                                        $subQ->orWhere('text_search', 'LIKE', "%{$keyword}%");
                                    }
                                }
                            });
                    });

                // Filter theo vehicleType nếu cần
                if ($vehicleType && $vehicleType !== 'oto') {
                    $categoryId = $this->getCategoryIdByVehicleType($vehicleType);
                    if ($categoryId) {
                        $queryBuilderOr->where(function ($q) use ($categoryId) {
                            $q->where('category_id', $categoryId)
                                ->orWhereHas('categories', function ($subQ) use ($categoryId) {
                                    $subQ->where('product_categories.id', $categoryId);
                                });
                        });
                    }
                }

                $products = $queryBuilderOr
                    ->with([
                        'translations',
                        'attributeValues.attribute',
                        'attributeValues.translations'
                    ])
                    ->take(100)
                    ->get();

                Log::info('DATABASE_FALLBACK_OR_RESULTS', [
                    'query' => $query,
                    'total' => $products->count()
                ]);
            }

            // ✅ Filter by keyword relevance để đảm bảo chất lượng kết quả
            $filteredProducts = $this->filterByKeywordRelevance($products, $query);

            Log::info('DATABASE_FALLBACK_FILTERED_RESULTS', [
                'query' => $query,
                'before_filter' => $products->count(),
                'after_filter' => $filteredProducts->count()
            ]);

            return $this->formatSearchResults($filteredProducts);
        } catch (Exception $e) {
            Log::error('DATABASE_FALLBACK_ERROR', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}
