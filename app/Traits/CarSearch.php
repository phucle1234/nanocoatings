<?php

namespace App\Traits;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttributeValueTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CarSearch
{
    /**
     * Lấy dữ liệu tìm kiếm xe cho view với cache
     * 
     * @return array ['manufacturers' => [], 'models' => [], 'years' => []]
     */
    public function getCarSearchData(): array
    {
        $currentLocale = app()->getLocale();
        $cacheKey = "car_search_data_{$currentLocale}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($currentLocale) {
                return $this->buildCarSearchData($currentLocale);
            });
        } catch (\Exception $e) {
            Log::error('CarSearch: Failed to get car search data', [
                'locale' => $currentLocale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getEmptySearchData();
        }
    }

    /**
     * Xóa cache dữ liệu tìm kiếm xe
     * 
     * @return void
     */
    public function clearCarSearchDataCache(): void
    {
        $languages = \App\Helpers\LanguageHelper::getLanguageCodes();

        foreach ($languages as $lang) {
            Cache::forget("car_search_data_{$lang}");
        }

        Log::info('CarSearch: Cache cleared for all languages');
    }

    /**
     * Build dữ liệu tìm kiếm xe từ database
     * 
     * @param string $locale
     * @return array
     */
    private function buildCarSearchData(string $locale): array
    {
        // Step 1: Lấy các attribute IDs cần thiết
        $attributeIds = $this->getRequiredAttributeIds();

        if (empty($attributeIds)) {
            Log::warning('CarSearch: Required attributes not found');
            return $this->getEmptySearchData();
        }

        // Step 2: Load TẤT CẢ data cần thiết một lần (tránh N+1)
        $allData = $this->loadAllCarSearchDataAtOnce($attributeIds, $locale);

        // Step 3: Build cấu trúc phân cấp
        return $this->buildHierarchicalData($allData);
    }

    /**
     * Lấy IDs của các attributes: manufacturer, model, production_year
     * 
     * @return array ['manufacturer' => id, 'model' => id, 'production_year' => id]
     */
    private function getRequiredAttributeIds(): array
    {
        $attributes = ProductAttribute::whereIn('code', ['manufacturer', 'model', 'production_year'])
            ->pluck('id', 'code')
            ->toArray();

        if (count($attributes) !== 3) {
            return [];
        }

        return $attributes;
    }

    /**
     * Load TẤT CẢ dữ liệu cần thiết một lần (optimize query)
     * 
     * @param array $attributeIds
     * @param string $locale
     * @return array
     */
    private function loadAllCarSearchDataAtOnce(array $attributeIds, string $locale): array
    {
        // Query 1: Lấy TẤT CẢ attribute values của 3 loại attributes
        $allAttributeValues = ProductAttributeValueTranslation::select(
            'product_attribute_value_translations.attribute_value_id',
            'product_attribute_value_translations.value',
            'product_attribute_values.attribute_id'
        )
            ->join('product_attribute_values', 'product_attribute_value_translations.attribute_value_id', '=', 'product_attribute_values.id')
            ->whereIn('product_attribute_values.attribute_id', array_values($attributeIds))
            ->where('product_attribute_values.is_active', true)
            ->where('product_attribute_value_translations.language', $locale)
            ->whereNotNull('product_attribute_value_translations.value')
            ->where('product_attribute_value_translations.value', '!=', '')
            ->get()
            ->groupBy('attribute_id');

        // Query 2: Lấy TẤT CẢ relationships giữa products và attribute values
        $productAttributeMap = DB::table('product_attribute_product')
            ->select('product_id', 'attribute_value_id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items->pluck('attribute_value_id')->toArray();
            })
            ->toArray();

        Log::info('CarSearch: Loaded all data', [
            'attribute_values_count' => $allAttributeValues->sum->count(),
            'products_count' => count($productAttributeMap)
        ]);

        return [
            'attribute_ids' => $attributeIds,
            'attribute_values' => $allAttributeValues,
            'product_attribute_map' => $productAttributeMap
        ];
    }

    /**
     * Build cấu trúc phân cấp: manufacturer -> model -> year
     * 
     * @param array $allData
     * @return array
     */
    private function buildHierarchicalData(array $allData): array
    {
        $attributeIds = $allData['attribute_ids'];
        $attributeValues = $allData['attribute_values'];
        $productAttributeMap = $allData['product_attribute_map'];

        // ✅ FIX: Lấy manufacturers và đảm bảo unique values
        $manufacturerCollection = $attributeValues->get($attributeIds['manufacturer'], collect());

        Log::info('CarSearch: buildHierarchicalData - Manufacturers raw', [
            'total_manufacturer_values' => $manufacturerCollection->count(),
            'sample' => $manufacturerCollection->take(10)->map(fn($m) => [
                'attribute_value_id' => $m->attribute_value_id,
                'value' => $m->value
            ])->toArray()
        ]);

        // ✅ FIX: Group by value để tránh duplicate, nhưng giữ lại tất cả attribute_value_ids
        $manufacturersByValue = $manufacturerCollection->groupBy('value');

        Log::info('CarSearch: buildHierarchicalData - Manufacturers grouped', [
            'unique_manufacturer_names' => $manufacturersByValue->keys()->toArray(),
            'count' => $manufacturersByValue->count()
        ]);

        // ✅ FIX: Tạo map: attribute_value_id -> manufacturer_name (giữ lại tất cả)
        $manufacturers = [];
        foreach ($manufacturerCollection as $manufacturer) {
            $manufacturers[$manufacturer->attribute_value_id] = $manufacturer->value;
        }

        // Sort by name
        asort($manufacturers);

        $models = $attributeValues->get($attributeIds['model'], collect())
            ->keyBy('attribute_value_id');

        $years = $attributeValues->get($attributeIds['production_year'], collect())
            ->keyBy('attribute_value_id');

        // Build reverse map: attribute_value_id -> [product_ids]
        $reverseMap = $this->buildReverseAttributeMap($productAttributeMap);

        // Build hierarchical structure
        $modelsData = [];
        $yearsData = [];
        $uniqueManufacturerNames = []; // ✅ Để track unique manufacturer names

        foreach ($manufacturers as $manufacturerValueId => $manufacturerName) {
            // Lấy products có manufacturer này
            $productsWithManufacturer = $reverseMap[$manufacturerValueId] ?? [];

            if (empty($productsWithManufacturer)) {
                // ✅ Vẫn thêm vào list nếu chưa có (có thể có products từ attribute_value_id khác)
                if (!in_array($manufacturerName, $uniqueManufacturerNames)) {
                    $uniqueManufacturerNames[] = $manufacturerName;
                    $modelsData[$manufacturerName] = [];
                }
                continue;
            }

            // ✅ Nếu manufacturer name đã có, merge models
            if (isset($modelsData[$manufacturerName])) {
                // Merge với models hiện có
                $existingModels = $modelsData[$manufacturerName];
            } else {
                $existingModels = [];
                $uniqueManufacturerNames[] = $manufacturerName;
            }

            // Tìm models cho manufacturer này
            $manufacturerModels = $this->findModelsForProducts($productsWithManufacturer, $models, $reverseMap);

            // ✅ Merge models (tránh duplicate)
            $mergedModels = array_unique(array_merge($existingModels, array_values($manufacturerModels)));
            $modelsData[$manufacturerName] = array_values($mergedModels);

            // Với mỗi model, tìm years
            foreach ($manufacturerModels as $modelValueId => $modelName) {
                $productsWithModel = array_intersect(
                    $productsWithManufacturer,
                    $reverseMap[$modelValueId] ?? []
                );

                $modelYears = $this->findYearsForProducts($productsWithModel, $years, $reverseMap);

                // ✅ Merge years nếu model đã có years từ trước
                if (isset($yearsData[$manufacturerName][$modelName])) {
                    $existingYears = $yearsData[$manufacturerName][$modelName];
                    $mergedYears = array_unique(array_merge($existingYears, array_values($modelYears)));
                    $yearsData[$manufacturerName][$modelName] = array_values($mergedYears);
                } else {
                    $yearsData[$manufacturerName][$modelName] = array_values($modelYears);
                }
            }
        }

        Log::info('CarSearch: buildHierarchicalData - Final result', [
            'manufacturers_count' => count($uniqueManufacturerNames),
            'manufacturers' => $uniqueManufacturerNames,
            'models_data_keys' => array_keys($modelsData),
            'sample_models' => array_slice($modelsData, 0, 3, true)
        ]);

        return [
            'manufacturers' => array_values($uniqueManufacturerNames), // ✅ Dùng unique names
            'models' => $modelsData,
            'years' => $yearsData
        ];
    }

    /**
     * Build reverse map: attribute_value_id -> [product_ids]
     * 
     * @param array $productAttributeMap
     * @return array
     */
    private function buildReverseAttributeMap(array $productAttributeMap): array
    {
        $reverseMap = [];

        foreach ($productAttributeMap as $productId => $attributeValueIds) {
            foreach ($attributeValueIds as $attributeValueId) {
                if (!isset($reverseMap[$attributeValueId])) {
                    $reverseMap[$attributeValueId] = [];
                }
                $reverseMap[$attributeValueId][] = $productId;
            }
        }

        return $reverseMap;
    }

    /**
     * Tìm models cho các products đã cho
     * 
     * @param array $productIds
     * @param \Illuminate\Support\Collection $allModels
     * @param array $reverseMap
     * @return array [modelValueId => modelName]
     */
    private function findModelsForProducts(array $productIds, $allModels, array $reverseMap): array
    {
        $foundModels = [];

        foreach ($allModels as $modelValueId => $modelData) {
            $productsWithThisModel = $reverseMap[$modelValueId] ?? [];
            $intersection = array_intersect($productIds, $productsWithThisModel);

            if (!empty($intersection)) {
                $foundModels[$modelValueId] = $modelData->value;
            }
        }

        asort($foundModels); // Sort alphabetically
        return $foundModels;
    }

    /**
     * Tìm years cho các products đã cho
     * 
     * @param array $productIds
     * @param \Illuminate\Support\Collection $allYears
     * @param array $reverseMap
     * @return array [yearValue, ...] sorted desc
     */
    private function findYearsForProducts(array $productIds, $allYears, array $reverseMap): array
    {
        $foundYears = [];

        foreach ($allYears as $yearValueId => $yearData) {
            $productsWithThisYear = $reverseMap[$yearValueId] ?? [];
            $intersection = array_intersect($productIds, $productsWithThisYear);

            if (!empty($intersection)) {
                $foundYears[] = $yearData->value;
            }
        }

        rsort($foundYears); // Sort descending (newest first)
        return $foundYears;
    }

    /**
     * Trả về empty data structure
     * 
     * @return array
     */
    private function getEmptySearchData(): array
    {
        return [
            'manufacturers' => [],
            'models' => [],
            'years' => []
        ];
    }

    /**
     * Lấy dữ liệu kích thước lốp xe cho dropdown
     * 
     * @return array ['wides' => [], 'rates' => [], 'diameters' => []]
     */
    public function getTireSizeData(): array
    {
        $currentLocale = app()->getLocale();
        $cacheKey = "tire_size_data_{$currentLocale}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($currentLocale) {
                return $this->buildTireSizeData($currentLocale);
            });
        } catch (\Exception $e) {
            Log::error('CarSearch: Failed to get tire size data', [
                'locale' => $currentLocale,
                'error' => $e->getMessage()
            ]);

            return [
                'wides' => [],
                'rates' => [],
                'diameters' => []
            ];
        }
    }

    /**
     * Build tire size data từ database
     * Trả về cả flat list VÀ hierarchical combinations
     * 
     * @param string $locale
     * @return array
     */
    private function buildTireSizeData(string $locale): array
    {
        // Lấy attribute IDs cho wide, rate, diameter
        $attributes = ProductAttribute::whereIn('code', ['wide', 'rate', 'diameter'])
            ->pluck('id', 'code')
            ->toArray();

        if (count($attributes) !== 3) {
            Log::warning('CarSearch: Tire size attributes not found');
            return [
                'wides' => [],
                'rates' => [],
                'diameters' => [],
                'combinations' => []
            ];
        }

        // ✅ LẤY ACTUAL COMBINATIONS từ products
        $combinations = DB::table('products as p')
            ->join('product_attribute_product as pap1', 'p.id', '=', 'pap1.product_id')
            ->join('product_attribute_values as pav1', 'pap1.attribute_value_id', '=', 'pav1.id')
            ->join('product_attribute_value_translations as pavt1', 'pav1.id', '=', 'pavt1.attribute_value_id')
            ->join('product_attribute_product as pap2', 'p.id', '=', 'pap2.product_id')
            ->join('product_attribute_values as pav2', 'pap2.attribute_value_id', '=', 'pav2.id')
            ->join('product_attribute_value_translations as pavt2', 'pav2.id', '=', 'pavt2.attribute_value_id')
            ->join('product_attribute_product as pap3', 'p.id', '=', 'pap3.product_id')
            ->join('product_attribute_values as pav3', 'pap3.attribute_value_id', '=', 'pav3.id')
            ->join('product_attribute_value_translations as pavt3', 'pav3.id', '=', 'pavt3.attribute_value_id')
            ->where('pav1.attribute_id', $attributes['wide'])
            ->where('pav2.attribute_id', $attributes['rate'])
            ->where('pav3.attribute_id', $attributes['diameter'])
            ->where('pavt1.language', $locale)
            ->where('pavt2.language', $locale)
            ->where('pavt3.language', $locale)
            ->select('pavt1.value as wide', 'pavt2.value as rate', 'pavt3.value as diameter')
            ->distinct()
            ->get();

        // Build hierarchical structure
        $hierarchy = [];
        $allWides = [];
        $allRates = [];
        $allDiameters = [];

        foreach ($combinations as $combo) {
            $wide = $combo->wide;
            $rate = $combo->rate;
            $diameter = $combo->diameter;

            if (!isset($hierarchy[$wide])) {
                $hierarchy[$wide] = [];
            }
            if (!isset($hierarchy[$wide][$rate])) {
                $hierarchy[$wide][$rate] = [];
            }
            $hierarchy[$wide][$rate][] = $diameter;

            $allWides[] = $wide;
            $allRates[] = $rate;
            $allDiameters[] = $diameter;
        }

        // Unique và sort
        $allWides = array_values(array_unique($allWides));
        $allRates = array_values(array_unique($allRates));
        $allDiameters = array_values(array_unique($allDiameters));

        sort($allWides, SORT_NUMERIC);
        sort($allRates, SORT_NUMERIC);
        sort($allDiameters, SORT_NUMERIC);

        return [
            'wides' => $allWides,
            'rates' => $allRates,
            'diameters' => $allDiameters,
            'combinations' => $hierarchy // ✅ Hierarchical data for dynamic dropdown
        ];
    }
    /**
     * Lấy dữ liệu tìm kiếm xe theo loại xe
     * 
     * @param string $vehicleType - 'xe-may', 'xe-tai', 'oto'
     * @return array ['manufacturers' => [], 'models' => [], 'years' => []]
     */
    public function getCarSearchDataByVehicleType(string $vehicleType = '04'): array
    {
        $currentLocale = app()->getLocale();
        $cacheKey = "car_search_data_{$vehicleType}_{$currentLocale}";

        try {
            // ✅ Thêm log để biết cache hit hay miss
            // if (Cache::has($cacheKey)) {
            //     Log::info('CarSearch: Using cached data', [
            //         'vehicle_type' => $vehicleType,
            //         'cache_key' => $cacheKey
            //     ]);
            // } else {
            //     Log::info('CarSearch: Cache miss, building new data', [
            //         'vehicle_type' => $vehicleType,
            //         'cache_key' => $cacheKey
            //     ]);
            // }

            return Cache::remember($cacheKey, 3600, function () use ($vehicleType, $currentLocale) {
                // ✅ Log này sẽ chỉ chạy khi cache miss (build data mới)
                // Log::info('CarSearch: Building car search data (cache miss)', [
                //     'vehicle_type' => $vehicleType,
                //     'locale' => $currentLocale
                // ]);

                return $this->buildCarSearchDataByVehicleType($vehicleType, $currentLocale);
            });
        } catch (\Exception $e) {
            Log::error('CarSearch: Failed to get car search data by vehicle type', [
                'vehicle_type' => $vehicleType,
                'locale' => $currentLocale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getEmptySearchData();
        }
    }

    /**
     * Lấy dữ liệu quy cách theo loại xe
     * 
     * @param string $vehicleType - 'xe-may', 'xe-tai', 'oto'
     * @return array ['wides' => [], 'rates' => [], 'diameters' => [], 'combinations' => []]
     */
    public function getTireSizeDataByVehicleType(string $vehicleType = '04'): array
    {
        $currentLocale = app()->getLocale();
        $cacheKey = "tire_size_data_{$vehicleType}_{$currentLocale}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($vehicleType, $currentLocale) {
                return $this->buildTireSizeDataByVehicleType($vehicleType, $currentLocale);
            });
        } catch (\Exception $e) {
            Log::error('CarSearch: Failed to get tire size data by vehicle type', [
                'vehicle_type' => $vehicleType,
                'locale' => $currentLocale,
                'error' => $e->getMessage()
            ]);

            return [
                'wides' => [],
                'rates' => [],
                'diameters' => [],
                'combinations' => []
            ];
        }
    }
    /**
     * Build dữ liệu tìm kiếm xe theo loại xe từ database
     * 
     * @param string $vehicleType
     * @param string $locale
     * @return array
     */
    private function buildCarSearchDataByVehicleType(string $vehicleType, string $locale): array
    {
        Log::info('CarSearch: buildCarSearchDataByVehicleType START', [
            'vehicle_type' => $vehicleType,
            'locale' => $locale
        ]);

        // Map loại xe với category code (thay vì slug)
        $categoryCode = $this->getCategoryCodeByVehicleType($vehicleType);

        Log::info('CarSearch: Category code mapped', [
            'vehicle_type' => $vehicleType,
            'category_code' => $categoryCode
        ]);

        if (!$categoryCode) {
            Log::warning('CarSearch: No category code found, using default data', [
                'vehicle_type' => $vehicleType
            ]);
            return $this->buildCarSearchData($locale);
        }

        // Lấy category ID
        $category = \App\Models\ProductCategory::where('code', $categoryCode)->first();

        if (!$category) {
            Log::warning('CarSearch: Category not found in database, using default data', [
                'vehicle_type' => $vehicleType,
                'category_code' => $categoryCode
            ]);
            return $this->buildCarSearchData($locale);
        }

        Log::info('CarSearch: Category found', [
            'vehicle_type' => $vehicleType,
            'category_code' => $categoryCode,
            'category_id' => $category->id,
            'category_name' => $category->name ?? 'N/A'
        ]);
        // Lấy attribute IDs
        $attributeIds = $this->getRequiredAttributeIds();

        if (empty($attributeIds)) {
            Log::warning('CarSearch: Required attributes not found');
            return $this->getEmptySearchData();
        }
        Log::info('CarSearch: Building car search data by vehicle type', [
            'vehicle_type' => $vehicleType,
            'category_code' => $categoryCode,
            'category_id' => $category->id,
            'attribute_ids' => $attributeIds
        ]);
        // Load data chỉ cho category này
        $allData = $this->loadCarSearchDataByCategory($attributeIds, $locale, $category->id, $vehicleType);
        $result = $this->buildHierarchicalData($allData);

        Log::info('CarSearch: buildCarSearchDataByVehicleType COMPLETE', [
            'vehicle_type' => $vehicleType,
            'manufacturers_count' => count($result['manufacturers'] ?? []),
            'models_count' => count($result['models'] ?? [])
        ]);

        return $result;
    }

    /**
     * Build tire size data theo loại xe từ database
     * 
     * @param string $vehicleType
     * @param string $locale
     * @return array
     */
    private function buildTireSizeDataByVehicleType(string $vehicleType, string $locale): array
    {
        Log::info('CarSearch: buildTireSizeDataByVehicleType START', [
            'vehicle_type' => $vehicleType,
            'locale' => $locale
        ]);

        // Map loại xe với category code
        $categoryCode = $this->getCategoryCodeByVehicleType($vehicleType);

        Log::info('CarSearch: Tire size - Category code mapped', [
            'vehicle_type' => $vehicleType,
            'category_code' => $categoryCode
        ]);

        if (!$categoryCode) {
            Log::warning('CarSearch: Tire size - No category code found, using default data', [
                'vehicle_type' => $vehicleType
            ]);
            // Fallback về data mặc định
            return $this->buildTireSizeData($locale);
        }

        // Lấy category ID
        $category = \App\Models\ProductCategory::where('code', $categoryCode)->first();

        if (!$category) {
            Log::warning('CarSearch: Tire size - Category not found in database, using default data', [
                'vehicle_type' => $vehicleType,
                'category_code' => $categoryCode,
                'available_codes' => \App\Models\ProductCategory::pluck('code')->toArray()
            ]);
            return $this->buildTireSizeData($locale);
        }

        Log::info('CarSearch: Tire size - Category found', [
            'vehicle_type' => $vehicleType,
            'category_code' => $categoryCode,
            'category_id' => $category->id,
            'category_name' => $category->name ?? 'N/A'
        ]);

        // Lấy attribute IDs cho wide, rate, diameter
        $attributes = ProductAttribute::whereIn('code', ['wide', 'rate', 'diameter'])
            ->pluck('id', 'code')
            ->toArray();

        if (count($attributes) !== 3) {
            Log::warning('CarSearch: Tire size attributes not found', [
                'found_attributes' => $attributes
            ]);
            return [
                'wides' => [],
                'rates' => [],
                'diameters' => [],
                'combinations' => []
            ];
        }

        Log::info('CarSearch: Tire size - Attributes found', [
            'attributes' => $attributes
        ]);

        // ✅ LẤY ACTUAL COMBINATIONS từ products của category này
        $combinations = DB::table('products as p')
            ->join('product_attribute_product as pap1', 'p.id', '=', 'pap1.product_id')
            ->join('product_attribute_values as pav1', 'pap1.attribute_value_id', '=', 'pav1.id')
            ->join('product_attribute_value_translations as pavt1', 'pav1.id', '=', 'pavt1.attribute_value_id')
            ->join('product_attribute_product as pap2', 'p.id', '=', 'pap2.product_id')
            ->join('product_attribute_values as pav2', 'pap2.attribute_value_id', '=', 'pav2.id')
            ->join('product_attribute_value_translations as pavt2', 'pav2.id', '=', 'pavt2.attribute_value_id')
            ->join('product_attribute_product as pap3', 'p.id', '=', 'pap3.product_id')
            ->join('product_attribute_values as pav3', 'pap3.attribute_value_id', '=', 'pav3.id')
            ->join('product_attribute_value_translations as pavt3', 'pav3.id', '=', 'pavt3.attribute_value_id')
            ->where('pav1.attribute_id', $attributes['wide'])
            ->where('pav2.attribute_id', $attributes['rate'])
            ->where('pav3.attribute_id', $attributes['diameter'])
            ->where('pavt1.language', $locale)
            ->where('pavt2.language', $locale)
            ->where('pavt3.language', $locale)
            ->where('p.is_active', true)
            // ✅ THÊM FILTER THEO CATEGORY
            ->where(function ($query) use ($category) {
                // Hỗ trợ cả category_id và many-to-many
                $query->where('p.category_id', $category->id)
                    ->orWhereExists(function ($subQuery) use ($category) {
                        $subQuery->select(DB::raw(1))
                            ->from('product_product_category')
                            ->whereColumn('product_product_category.product_id', 'p.id')
                            ->where('product_product_category.product_category_id', $category->id);
                    });
            })
            ->select('pavt1.value as wide', 'pavt2.value as rate', 'pavt3.value as diameter')
            ->distinct()
            ->get();

        // Build hierarchical structure (giống như buildTireSizeData)
        $hierarchy = [];
        $allWides = [];
        $allRates = [];
        $allDiameters = [];

        foreach ($combinations as $combo) {
            $wide = $combo->wide;
            $rate = $combo->rate;
            $diameter = $combo->diameter;

            if (!isset($hierarchy[$wide])) {
                $hierarchy[$wide] = [];
            }
            if (!isset($hierarchy[$wide][$rate])) {
                $hierarchy[$wide][$rate] = [];
            }
            $hierarchy[$wide][$rate][] = $diameter;

            $allWides[] = $wide;
            $allRates[] = $rate;
            $allDiameters[] = $diameter;
        }

        // Unique và sort
        $allWides = array_values(array_unique($allWides));
        $allRates = array_values(array_unique($allRates));
        $allDiameters = array_values(array_unique($allDiameters));

        sort($allWides, SORT_NUMERIC);
        sort($allRates, SORT_NUMERIC);
        sort($allDiameters, SORT_NUMERIC);

        return [
            'wides' => $allWides,
            'rates' => $allRates,
            'diameters' => $allDiameters,
            'combinations' => $hierarchy
        ];
    }
    /**
     * Load car search data theo category
     * 
     * @param array $attributeIds
     * @param string $locale
     * @param int $categoryId
     * @return array
     */
    private function loadCarSearchDataByCategory(array $attributeIds, string $locale, int $categoryId, string $vehicleType): array
    {
        $allAttributeValues = ProductAttributeValueTranslation::select(
            'product_attribute_value_translations.attribute_value_id',
            'product_attribute_value_translations.value',
            'product_attribute_values.attribute_id'
        )
            ->join('product_attribute_values', 'product_attribute_value_translations.attribute_value_id', '=', 'product_attribute_values.id')
            ->join('product_attribute_product', 'product_attribute_values.id', '=', 'product_attribute_product.attribute_value_id')
            ->join('products', 'product_attribute_product.product_id', '=', 'products.id')
            ->whereIn('product_attribute_values.attribute_id', array_values($attributeIds))
            ->where('product_attribute_values.is_active', true)
            ->where('product_attribute_value_translations.language', $locale)
            ->whereNotNull('product_attribute_value_translations.value')
            ->where('product_attribute_value_translations.value', '!=', '')
            ->where('products.is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->where('products.category_id', $categoryId)
                    ->orWhereExists(function ($subQuery) use ($categoryId) {
                        $subQuery->select(DB::raw(1))
                            ->from('product_product_category')
                            ->whereColumn('product_product_category.product_id', 'products.id')
                            ->where('product_product_category.product_category_id', $categoryId);
                    });
            })
            ->where(function ($query) use ($vehicleType) {
                $query->whereNull('product_attribute_values.vehicle_type')
                    ->orWhere('product_attribute_values.vehicle_type', 'all')
                    ->orWhere('product_attribute_values.vehicle_type', $vehicleType);
            })
            ->distinct()
            ->get()
            ->groupBy('attribute_id');

        $productAttributeMap = DB::table('product_attribute_product')
            ->join('product_attribute_values', 'product_attribute_product.attribute_value_id', '=', 'product_attribute_values.id')
            ->join('products', 'product_attribute_product.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->where('products.category_id', $categoryId)
                    ->orWhereExists(function ($subQuery) use ($categoryId) {
                        $subQuery->select(DB::raw(1))
                            ->from('product_product_category')
                            ->whereColumn('product_product_category.product_id', 'products.id')
                            ->where('product_product_category.product_category_id', $categoryId);
                    });
            })
            ->where(function ($query) use ($vehicleType) {
                $query->whereNull('product_attribute_values.vehicle_type')
                    ->orWhere('product_attribute_values.vehicle_type', 'all')
                    ->orWhere('product_attribute_values.vehicle_type', $vehicleType);
            })
            ->select('product_attribute_product.product_id', 'product_attribute_product.attribute_value_id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items->pluck('attribute_value_id')->toArray();
            })
            ->toArray();

        return [
            'attribute_ids' => $attributeIds,
            'attribute_values' => $allAttributeValues,
            'product_attribute_map' => $productAttributeMap
        ];
    }
    /**
     * Map loại xe với category code
     * 
     * @param string $vehicleType
     * @return string|null
     */
    protected function getCategoryCodeByVehicleType(string $vehicleType): ?string
    {
        $mapping = [
            'xe-may' => '03',  // Code thực tế trong database
            'xe-tai' => '01',
            'oto' => '04'
        ];

        $result = $mapping[$vehicleType] ?? null;

        Log::info('CarSearch: getCategoryCodeByVehicleType', [
            'vehicle_type' => $vehicleType,
            'mapped_code' => $result,
            'available_mappings' => array_keys($mapping)
        ]);

        return $result;
    }

    // ============================================================================
    // ✨ NEW OPTIMIZED METHODS USING VEHICLE_FITMENTS TABLE
    // ============================================================================

    /**
     * Lấy car search data sử dụng vehicle_fitments table (TỐI ƯU)
     * 
     * @param string $vehicleType
     * @return array
     */
    public function getCarSearchDataFromFitments(string $vehicleType = '04'): array
    {
        $currentLocale = app()->getLocale();
        $cacheKey = "car_search_fitments_{$vehicleType}_{$currentLocale}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($vehicleType) {
                return $this->buildCarSearchDataFromFitments($vehicleType);
            });
        } catch (\Exception $e) {
            Log::error('CarSearch: Failed to get car search data from fitments', [
                'vehicle_type' => $vehicleType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getEmptySearchData();
        }
    }

    /**
     * Build car search data từ fitments table
     * 
     * @param string $vehicleType
     * @return array
     */
    private function buildCarSearchDataFromFitments(string $vehicleType): array
    {
        $categoryCode = $this->getCategoryCodeByVehicleType($vehicleType);

        if (!$categoryCode) {
            return $this->getEmptySearchData();
        }

        $category = \App\Models\ProductCategory::where('code', $categoryCode)->first();

        if (!$category) {
            return $this->getEmptySearchData();
        }

        // ✅ Query 1: Get all manufacturers
        $manufacturers = DB::table('product_vehicle_fitments as pvf')
            ->join('products as p', 'pvf.product_id', '=', 'p.id')
            ->where('p.is_active', true)
            ->where(function ($query) use ($category) {
                $query->where('p.category_id', $category->id)
                    ->orWhereExists(function ($subQuery) use ($category) {
                        $subQuery->select(DB::raw(1))
                            ->from('product_product_category')
                            ->whereColumn('product_product_category.product_id', 'p.id')
                            ->where('product_product_category.product_category_id', $category->id);
                    });
            })
            ->whereNotNull('pvf.manufacturer')
            ->select('pvf.manufacturer')
            ->distinct()
            ->orderBy('pvf.manufacturer')
            ->pluck('manufacturer')
            ->toArray();

        // ✅ Query 2: Get models per manufacturer
        $modelsData = [];
        foreach ($manufacturers as $manufacturer) {
            $models = DB::table('product_vehicle_fitments as pvf')
                ->join('products as p', 'pvf.product_id', '=', 'p.id')
                ->where('pvf.manufacturer', $manufacturer)
                ->where('p.is_active', true)
                ->where(function ($query) use ($category) {
                    $query->where('p.category_id', $category->id)
                        ->orWhereExists(function ($subQuery) use ($category) {
                            $subQuery->select(DB::raw(1))
                                ->from('product_product_category')
                                ->whereColumn('product_product_category.product_id', 'p.id')
                                ->where('product_product_category.product_category_id', $category->id);
                        });
                })
                ->whereNotNull('pvf.model')
                ->select('pvf.model')
                ->distinct()
                ->orderBy('pvf.model')
                ->pluck('model')
                ->toArray();

            $modelsData[$manufacturer] = $models;
        }

        // ✅ Query 3: Get years per manufacturer-model
        $yearsData = [];
        foreach ($manufacturers as $manufacturer) {
            $yearsData[$manufacturer] = [];

            foreach ($modelsData[$manufacturer] as $model) {
                $years = DB::table('product_vehicle_fitments as pvf')
                    ->join('products as p', 'pvf.product_id', '=', 'p.id')
                    ->where('pvf.manufacturer', $manufacturer)
                    ->where('pvf.model', $model)
                    ->where('p.is_active', true)
                    ->where(function ($query) use ($category) {
                        $query->where('p.category_id', $category->id)
                            ->orWhereExists(function ($subQuery) use ($category) {
                                $subQuery->select(DB::raw(1))
                                    ->from('product_product_category')
                                    ->whereColumn('product_product_category.product_id', 'p.id')
                                    ->where('product_product_category.product_category_id', $category->id);
                            });
                    })
                    ->whereNotNull('pvf.year')
                    ->select('pvf.year')
                    ->distinct()
                    ->orderByDesc('pvf.year')
                    ->pluck('year')
                    ->toArray();

                $yearsData[$manufacturer][$model] = $years;
            }
        }

        Log::info('CarSearch: buildCarSearchDataFromFitments completed', [
            'vehicle_type' => $vehicleType,
            'manufacturers_count' => count($manufacturers),
            'total_models' => array_sum(array_map('count', $modelsData)),
        ]);

        return [
            'manufacturers' => $manufacturers,
            'models' => $modelsData,
            'years' => $yearsData
        ];
    }

    /**
     * Clear cache cho fitments-based search data
     */
    public function clearFitmentsSearchCache(): void
    {
        $vehicleTypes = ['oto', 'xe-may', 'xe-tai'];
        $languages = \App\Helpers\LanguageHelper::getLanguageCodes();

        foreach ($vehicleTypes as $type) {
            foreach ($languages as $lang) {
                Cache::forget("car_search_fitments_{$type}_{$lang}");
            }
        }

        Log::info('CarSearch: Fitments cache cleared');
    }
}
