<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExternalProductImporter
{
    /** @var array<int, true> attribute_value_ids đã gán trong lần import hiện tại */
    private array $currentProductAttributeValueIds = [];

    /** @var array<string, true> */
    private array $mappedApiFieldSet = [];

    /** @var array<int, string> productId => itemNo */
    private array $pendingPriceUpdates = [];

    private const FORCED_BESTSELLER_IMAGES = [
        '21064801' => '/storage/images/1767578884_0_Tyg6CLiWqD.png',
        '0103559'  => '/storage/images/1767577950_0_FkE0mF5rmm.png',
        '22064191' => '/storage/images/1775011669_0_QdZ9P5OdZ1.png', //xe tai
    ];
    /** Map API field → attribute code trong DB */
    private const ATTRIBUTE_MAP = [
        // Các thuộc tính cũ của bạn
        'tread'             => 'tread',
        'wide'              => 'wide',
        'width'             => 'wide',
        'rate'              => 'rate',
        'wheel_diameter'    => 'diameter',
        'ply_rating'        => 'ply_rating',
        'typed'             => 'tire_type',
        'loaded'            => 'load_index_number',
        'speed'             => 'speed_index',
        'deep'              => 'tread_depth',
        'warranty'          => 'warranty',
        'size'              => 'size',
        'grip'              => 'road_grip',
        'heat'              => 'heat_resistance',
        'etrto'             => 'etrto',
        'inches'            => 'size_inches',
        'outer_diameter'    => 'outer_diameter',
        'rim_diameter'      => 'rim_diameter',
        'sidewall_width'    => 'sidewall_width',
        'rim_width'         => 'rim_width',
        'tire_pressure'     => 'standard_pressure',
        'van'               => 'valve',
        'thickness'         => 'body_thickness',
        'length'            => 'folded_length',

        // --- BỔ SUNG CÁC TRƯỜNG CÒN THIẾU TỪ DANH SÁCH ---
        'finger'            => 'finger',
        'manufacturer'      => 'manufacturer',
        'model'             => 'model',
        'diameter'          => 'diameter',
        'production_type'   => 'production_type',
        'weight'            => 'weight',
        'speed_rating'      => 'speed_rating',
        'road_grip'         => 'road_grip',
        'heat_resistance'   => 'heat_resistance',
        'production_year'   => 'production_year',
        'product_features'  => 'product_features',
        'tire_type'         => 'tire_type',
        'speed_index'       => 'speed_index',
        'tread_depth'       => 'tread_depth',
        'load_index_number' => 'load_index_number',
        'tire_line'         => 'tire_line',
        'tire_pattern'      => 'tire_pattern',
        'tire_color_group'  => 'tire_color_group',
        'size_inches'       => 'size_inches',
        'etrto_code'        => 'etrto_code',
        'standard_pressure' => 'standard_pressure',
        'folded_length'     => 'folded_length',
        'body_thickness'    => 'body_thickness',
        'valve'             => 'valve',
    ];

    /** Category codes có "sub" → vehicle fitments */
    private const FITMENT_CATEGORY_CODES = ['03', '04'];
    private const HIDDEN_ATTRIBUTE_CODES = [
        'uom',
        'item_category_name',
        'item_group_name',
        'size',
        'item_no',
        'item_name',
        'finger',
        'production_type',
        'manufacturer',
        'model',
        'year_production',
        'item_model_code',
        'picture2',
        'picture3',
        'picture4',
        'picture5',
        'characteristic',
        'status',
    ];

    private const SYSTEM_FIELDS = [
        // Product identity / translation source
        'item_no'            => true,
        'item_name'          => true,
        'item_name_en'       => true,

        // External category / group / model metadata (không phải product attribute)
        'item_category_code' => true,
        'item_category_code_sub' => true,
        'item_category_name' => true,
        'item_group_code'    => true,
        'item_group_name'    => true,
        'item_model_code'    => true,
        'item_model_name'    => true,

        // Nested / media / auxiliary payload
        'sub'                => true,
        'images'             => true,
        'picture'            => true,
        'model'              => true,
        'car'                => true,
        'warranty_en'        => true,

        // Import control flags
        'full_sync'          => true,
        'is_full_sync'       => true,
        '_full_sync'         => true,
        'sync_mode'          => true,
    ];


    /** @var array  cache attribute code → attribute id */
    private array $attributeCache = [];

    /** @var array  cache "attrId:value" → attribute_value id */
    private array $attributeValueCache = [];

    /** @var array  code → category id từ DB */
    private array $categoryCodeMap = [];

    /** @var ExternalApiService|null  Gọi API lấy giá (priceg) khi import */
    private ?ExternalApiService $api = null;

    /** Cách gọi API priceg: 'post' (body item_no) hoặc 'get' (query param). Định nghĩa tại đây, không dùng config. */
    private const PRICEG_HTTP_METHOD = 'post';

    /** @var int[] */
    private array $stats = [
        'created'  => 0,
        'updated'  => 0,
        'skipped'  => 0,
        'fitments' => 0,
    ];

    /** @var array{full_sync: bool} */
    private array $importOptions = [
        'full_sync' => false,
    ];

    private bool $defaultFullSync = false;

    /** Mặc định chặn auto-create category; chỉ bật khi chủ động set true */
    private bool $allowCategoryAutoCreate = false;

    public function __construct(
        array $categoryCodeMap = [],
        ?ExternalApiService $api = null,
        bool $defaultFullSync = false,
        bool $allowCategoryAutoCreate = false
    ) {
        $this->categoryCodeMap         = $categoryCodeMap;
        $this->api                     = $api;
        $this->mappedApiFieldSet       = array_fill_keys(array_keys(self::ATTRIBUTE_MAP), true);
        $this->defaultFullSync         = $defaultFullSync;
        $this->allowCategoryAutoCreate = $allowCategoryAutoCreate;
        $this->importOptions           = ['full_sync' => $defaultFullSync];
    }

	// ─────────────────────────────────────────────────────────────────────────
	// Public API
	// ─────────────────────────────────────────────────────────────────────────

    /**
     * Import 1 batch sản phẩm (kết quả từ 1 lần gọi API).
     *
     * @param  array   $products           Mảng sản phẩm từ API
     * @param  string  $categoryCode       Code danh mục cha (e.g. "01")
     * @return array{created,updated,skipped,fitments}
     */
    public function import(array $products, string $categoryCode, array $options = []): array
    {
        $this->ensureCategoryCodeMapLoaded();
        $this->stats               = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'fitments' => 0];
        $this->pendingPriceUpdates = [];
        $this->importOptions       = $this->normalizeImportOptions($options);

        foreach ($products as $item) {
            try {
                $this->importOne($item, $categoryCode);
            } catch (\Throwable $e) {
                $this->stats['skipped']++;
                $this->logSkippedItem($item, [
                    'reason'      => 'exception',
                    'error'       => $e->getMessage(),
                    'exception'   => get_class($e),
                    'file'        => $e->getFile(),
                    'line'        => $e->getLine(),
                    'full_sync'   => $this->resolveFullSyncFlag($item),
                    'trace_short' => collect(explode("\n", $e->getTraceAsString()))->take(8)->values()->all(),
                ]);
            }
        }

        $this->flushPendingPrices();

        return $this->stats;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function setDefaultFullSync(bool $defaultFullSync): self
    {
        $this->defaultFullSync = $defaultFullSync;
        $this->importOptions   = $this->normalizeImportOptions($this->importOptions);

        return $this;
    }

    public function setAllowCategoryAutoCreate(bool $allowCategoryAutoCreate): self
    {
        $this->allowCategoryAutoCreate = $allowCategoryAutoCreate;

        return $this;
    }

    private function normalizeImportOptions(array $options = []): array
    {
        return [
            'full_sync' => $this->toBool($options['full_sync'] ?? $this->defaultFullSync),
        ];
    }

    private function resolveFullSyncFlag(array $item): bool
    {
        foreach (['full_sync', 'is_full_sync', '_full_sync'] as $key) {
            if (array_key_exists($key, $item)) {
                return $this->toBool($item[$key]);
            }
        }

        if (array_key_exists('sync_mode', $item)) {
            $syncMode = strtolower(trim((string) $item['sync_mode']));
            if (in_array($syncMode, ['full', 'full_sync'], true)) {
                return true;
            }
            if (in_array($syncMode, ['partial', 'patch'], true)) {
                return false;
            }
        }

        return (bool) ($this->importOptions['full_sync'] ?? $this->defaultFullSync);
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y', 'on', 'full', 'full_sync'], true);
        }

        return false;
    }

    private function shouldAutoCreateCategories(): bool
    {
        return $this->allowCategoryAutoCreate;
    }

    private function logSkippedItem(array $item, array $context = []): void
    {
        $payloadPreview = [
            'item_no'                 => $item['item_no'] ?? null,
            'item_name'               => $item['item_name'] ?? null,
            'item_category_code'      => $item['item_category_code'] ?? null,
            'item_category_code_sub'  => $item['item_category_code_sub'] ?? null,
            'item_model_code'         => $item['item_model_code'] ?? null,
            'item_model_name'         => $item['item_model_name'] ?? null,
            'uom'                     => $item['uom'] ?? null,
            'size'                    => $item['size'] ?? null,
            'typed'                   => $item['typed'] ?? null,
            'sub_count'               => is_array($item['sub'] ?? null) ? count($item['sub']) : 0,
        ];

        Log::channel('import_external')->warning('[Product] Bỏ qua sản phẩm khi import', array_merge([
            'item_no'          => $item['item_no'] ?? '?',
            'item_name'        => $item['item_name'] ?? null,
            'payload_preview'  => $payloadPreview,
        ], $context));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Core import
    // ─────────────────────────────────────────────────────────────────────────

    private function importOne(array $item, string $categoryCode): void
    {
        $statsBefore                      = $this->stats;
        $pendingPriceUpdatesBefore        = $this->pendingPriceUpdates;
        $currentAttributeValueIdsBefore   = $this->currentProductAttributeValueIds;

        try {
            DB::transaction(function () use ($item, $categoryCode) {
                $this->importOneWithinTransaction($item, $categoryCode);
            });
        } catch (\Throwable $e) {
            $this->stats                          = $statsBefore;
            $this->pendingPriceUpdates            = $pendingPriceUpdatesBefore;
            $this->currentProductAttributeValueIds = $currentAttributeValueIdsBefore;

            throw $e;
        }
    }

    private function importOneWithinTransaction(array $item, string $categoryCode): void
    {
        $itemNo   = trim((string) ($item['item_no'] ?? ''));
        $itemName = trim((string) ($item['item_name'] ?? ''));
        if ($itemName === '' && is_array($item['model'] ?? null)) {
            $itemName = trim((string) (($item['model']['name'] ?? '') ?: ($item['model']['name_en'] ?? '')));
        }

        if ($itemNo === '' || $itemName === '') {
            $missingFields = [];
            if ($itemNo === '') {
                $missingFields[] = 'item_no';
            }
            if ($itemName === '') {
                $missingFields[] = 'item_name';
            }

            $this->stats['skipped']++;
            $this->logSkippedItem($item, [
                'reason'         => 'missing_required_fields',
                'missing_fields' => $missingFields,
                'full_sync'      => $this->resolveFullSyncFlag($item),
            ]);
            return;
        }

        $fullSync = $this->resolveFullSyncFlag($item);
        $this->currentProductAttributeValueIds = [];

        $sku       = $itemNo;
        $imageUrls = $this->extractImages($item);

        $isForcedBestseller = array_key_exists($itemNo, self::FORCED_BESTSELLER_IMAGES);
        if ($isForcedBestseller) {
            $forcedImage = self::FORCED_BESTSELLER_IMAGES[$itemNo];
            $imageUrls   = array_values(array_unique(array_merge([$forcedImage], $imageUrls)));
        }

        $existing = DB::table('products')->where('code', $itemNo)->lockForUpdate()->first();

        $effectiveCategoryCodeForId = trim((string) ($item['item_category_code'] ?? $categoryCode));
        if ($effectiveCategoryCodeForId === '') {
            $effectiveCategoryCodeForId = $categoryCode;
        }

        $categoryId = $this->categoryCodeMap[$effectiveCategoryCodeForId] ?? null;
        if ($categoryId === null) {
            Log::channel('import_external')->warning('[Product] Danh mục không có trong map, dùng category_id=1', [
                'item_no'         => $itemNo,
                'category_code'   => $effectiveCategoryCodeForId,
                'available_codes' => array_keys($this->categoryCodeMap),
            ]);
            $categoryId = 1;
        }

        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update([
                'image_urls'    => json_encode($imageUrls),
                'category_id'   => $categoryId,
                'is_active'     => ($item['status'] ?? '1') === '1' ? true : false,
                'is_featured'   => $item['item_type']['popular'] ?? false,
                'is_new'        => $item['item_type']['new'] ?? false,
                'is_bestseller' => $isForcedBestseller ? 1 : ($existing->is_bestseller ?? 0),
                'updated_at'    => now(),
            ]);
            $productId = $existing->id;
            $this->stats['updated']++;
        } else {
            $productId = DB::table('products')->insertGetId([
                'code'               => $itemNo,
                'sku'                => $this->uniqueSku($sku),
                'category_id'        => $categoryId,
                'price'              => 0,
                'sale_price'         => null,
                'stock_quantity'     => 0,
                'min_stock_quantity' => 0,
                'is_active'          => ($item['status'] ?? '1') === '1' ? true : false,
                'is_featured'        => $item['item_type']['popular'] ?? false,
                'is_new'             => $item['item_type']['new'] ?? false,
                'is_bestseller'      => $isForcedBestseller ? 1 : 0,
                'view_count'         => 0,
                'sort_order'         => 0,
                'image_urls'         => json_encode($imageUrls),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $this->stats['created']++;
        }

        $primaryCategoryId = $this->attachCategories($productId, $categoryCode, $item);

        if ($primaryCategoryId !== null && (int) $primaryCategoryId !== (int) $categoryId) {
            DB::table('products')->where('id', $productId)->update([
                'category_id' => $primaryCategoryId,
                'updated_at'  => now(),
            ]);

            $categoryId = $primaryCategoryId;
        }

        // Payload mới: description/features lấy từ model.overview/feature, thiếu thì để rỗng.
        // Không còn fallback theo category.
        $this->upsertTranslations($productId, $itemName, $item, $categoryCode);

        $this->assignModelCharacteristicAttribute($productId, $item, $categoryCode);

        $warrantyEn = trim((string) ($item['warranty_en'] ?? ''));

        foreach (self::ATTRIBUTE_MAP as $apiField => $attributeCode) {
            $value = $item[$apiField] ?? '';
            // Một số field (vd. model) trong payload mới là object/array.
            // Chỉ lấy giá trị string có ý nghĩa, tránh Array-to-string.
            if (is_array($value)) {
                if ($apiField === 'model') {
                    $value = (string) (($value['name'] ?? '') ?: ($value['code'] ?? ''));
                } else {
                    continue;
                }
            }
            if (!$this->isValid($value)) {
                continue;
            }

            $valueEn = $attributeCode === 'warranty' ? $warrantyEn : '';
            $this->assignAttributeValue($productId, $attributeCode, (string) $value, $categoryCode, $valueEn);
        }

        foreach ($item as $apiField => $value) {
            if (isset($this->mappedApiFieldSet[$apiField])) {
                continue;
            }

            if (isset(self::SYSTEM_FIELDS[$apiField])) {
                continue;
            }

            // Payload mới có thể có nested object/array (vd. car, model, picture...).
            // Không map các object này vào attribute value (tránh Array-to-string).
            if (is_array($value)) {
                continue;
            }

            if (!$this->isValid($value)) {
                continue;
            }

            $this->assignAttributeValue($productId, $apiField, (string) $value, $categoryCode);
        }

        $this->ensureSizeAttributeFromWideRateDiameter($productId, $item, $categoryCode);
        $this->ensureWideRateDiameterFromSize($productId, $item, $categoryCode);

        $this->syncProductAttributes($productId, $fullSync);

        $rawCode              = trim((string) ($item['item_category_code'] ?? $categoryCode)) ?: $categoryCode;
        $parentCodeForFitment = strlen($rawCode) >= 2 ? substr($rawCode, 0, 2) : $rawCode;

        // Vehicle fitment payload có thể nằm ở `sub` (cũ) hoặc `car` (mới).
        $fitmentPayload = null;
        if (is_array($item['sub'] ?? null)) {
            $fitmentPayload = $item['sub'];
        } elseif (is_array($item['car'] ?? null)) {
            $fitmentPayload = $item['car'];
        }

        if (in_array($parentCodeForFitment, self::FITMENT_CATEGORY_CODES, true) && is_array($fitmentPayload)) {
            $this->syncFitments($productId, $fitmentPayload, $fullSync);
        }

        if ($this->api !== null) {
            $this->pendingPriceUpdates[$productId] = $itemNo;
        }
    }
    /**
     * Xóa khỏi product_attribute_product những attribute_value
     * không còn được gán trong lần import này.
     *
     * Ví dụ: trước đây sản phẩm có warranty="5 năm", giờ truyền warranty=null
     * → pivot row của warranty bị xóa.
     */
    private function syncProductAttributes(int $productId, bool $strict = false): void
    {
        $keepIds = array_keys($this->currentProductAttributeValueIds);

        $query = DB::table('product_attribute_product')
            ->where('product_id', $productId);

        if ($keepIds === []) {
            if (!$strict) {
                return;
            }

            $deleted = $query->delete();
        } else {
            $deleted = $query->whereNotIn('attribute_value_id', $keepIds)->delete();
        }

        if ($deleted > 0) {
            Log::channel('import_external')->debug('[Attribute] Đã sync attribute cũ khỏi sản phẩm', [
                'product_id' => $productId,
                'deleted'    => $deleted,
                'kept_ids'   => $keepIds,
                'strict'     => $strict,
            ]);
        }
    }
    /**
     * Đồng bộ vehicle fitments: xóa những xe không còn trong danh sách mới,
     * giữ lại / thêm mới những xe có trong $subs.
     *
     * Thay thế vòng lặp upsertFitment() cũ để xử lý trường hợp sub bị rút bớt.
     */
    private function syncFitments(int $productId, array $subs, bool $fullSync = false): void
    {
        // ── Bước 1: Build danh sách fitment mới (expand year range) ──────────
        $newFitments = [];

        foreach ($subs as $sub) {
            foreach ($this->expandYearProduction($sub['year_production'] ?? '') as $yearValue) {
                $manufacturer = trim((string) ($sub['automaker'] ?? $sub['manufacturer'] ?? ''));
                $model        = trim((string) ($sub['model'] ?? ''));
                $year         = $yearValue === null ? null : trim((string) $yearValue);

                $manufacturer = ($manufacturer === '' || $manufacturer === '-') ? null : $manufacturer;
                $model        = ($model === '' || $model === '-') ? null : $model;
                $year         = ($year === '' || $year === '-') ? null : $year;

                if ($manufacturer === null && $model === null && $year === null) {
                    continue;
                }

                $key               = implode('|', [$manufacturer ?? '', $model ?? '', $year ?? '']);
                $newFitments[$key] = [
                    'manufacturer' => $manufacturer,
                    'model'        => $model,
                    'year'         => $year,
                ];
            }
        }

        $existingRows = DB::table('product_vehicle_fitments')
            ->where('product_id', $productId)
            ->select('id', 'manufacturer', 'model', 'year')
            ->get();

        $existingByKey = [];
        foreach ($existingRows as $row) {
            $key                 = implode('|', [$row->manufacturer ?? '', $row->model ?? '', $row->year ?? '']);
            $existingByKey[$key] = $row->id;
        }

        if ($fullSync) {
            $keysToDelete = array_diff(array_keys($existingByKey), array_keys($newFitments));
            if (!empty($keysToDelete)) {
                $idsToDelete = array_map(fn($k) => $existingByKey[$k], $keysToDelete);
                DB::table('product_vehicle_fitments')
                    ->where('product_id', $productId)
                    ->whereIn('id', $idsToDelete)
                    ->delete();

                Log::channel('import_external')->debug('[Fitment] Đã xóa fitment cũ theo full_sync', [
                    'product_id' => $productId,
                    'deleted'    => count($idsToDelete),
                ]);
            }
        } else {
            Log::channel('import_external')->debug('[Fitment] Partial sync: không xóa fitment cũ', [
                'product_id' => $productId,
                'incoming'   => count($newFitments),
            ]);
        }

        $now      = now();
        $toInsert = [];
        foreach ($newFitments as $key => $fitment) {
            if (isset($existingByKey[$key])) {
                continue;
            }
            $toInsert[] = [
                'product_id'   => $productId,
                'manufacturer' => $fitment['manufacturer'],
                'model'        => $fitment['model'],
                'year'         => $fitment['year'],
                'is_verified'  => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (!empty($toInsert)) {
            DB::table('product_vehicle_fitments')->insert($toInsert);
            $this->stats['fitments'] += count($toInsert);
        }
    }

    /**
     * Gọi API priceg theo item_no và cập nhật cột price cho sản phẩm.
     */
    private function updateProductPriceFromApi(int $productId, string $itemNo): void
    {
        $price = $this->api->getPriceByItemNo($itemNo, self::PRICEG_HTTP_METHOD);
        if ($price !== null && $price >= 0) {
            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'price'      => $price,
                    'updated_at' => now(),
                ]);
            Log::channel('import_external')->debug('[Product] Đã cập nhật giá từ API', [
                'product_id' => $productId,
                'item_no'    => $itemNo,
                'price'      => $price,
            ]);
        }
    }

    private function flushPendingPrices(): void
    {
        if ($this->api === null || $this->pendingPriceUpdates === []) {
            return;
        }

        foreach ($this->pendingPriceUpdates as $productId => $itemNo) {
            $this->updateProductPriceFromApi($productId, $itemNo);
        }

        $this->pendingPriceUpdates = [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Translations (description, features, short_description, text_search)
    // ─────────────────────────────────────────────────────────────────────────

    private function plainTextToHtmlWithBreaks(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Views render description/features as raw HTML, so we must escape here.
        // Convert newlines to <br> to preserve line breaks.
        return nl2br(e($text));
    }

    private function upsertTranslations(int $productId, string $itemName, array $item, string $effectiveCategoryCode): void
    {
        $shortDesc  = $this->buildShortDescription($item);
        $now        = now();
        $itemNo     = trim((string) ($item['item_no'] ?? ''));
        $itemNameEn = trim((string) ($item['item_name_en'] ?? '')) ?: $itemName;
        $baseSlug   = Str::slug($itemName) . '-' . strtolower($itemNo);

        $existingSlugs = DB::table('product_translations')
            ->where('product_id', $productId)
            ->whereIn('language', ['vi', 'en'])
            ->pluck('slug', 'language')
            ->toArray();

        $rows = [];
        foreach (['vi', 'en'] as $lang) {
            $nameForLang = $lang === 'en' ? $itemNameEn : $itemName;
            // Payload mới: description/features lấy từ model.overview/feature theo ngôn ngữ.
            // Không fallback theo category; thiếu thì để rỗng.
            $modelPayload = is_array($item['model'] ?? null) ? $item['model'] : null;
            $description = '';
            $features    = '';

            if ($modelPayload) {
                if ($lang === 'en') {
                    $description = $this->plainTextToHtmlWithBreaks((string) ($modelPayload['overview_en'] ?? ''));
                    $features    = $this->plainTextToHtmlWithBreaks((string) ($modelPayload['feature_en'] ?? ''));
                } else {
                    $description = $this->plainTextToHtmlWithBreaks((string) ($modelPayload['overview'] ?? ''));
                    $features    = $this->plainTextToHtmlWithBreaks((string) ($modelPayload['feature'] ?? ''));
                }
                $description = $this->appendCharacteristicBlockToDescription($description, $modelPayload, $lang);
            }
            $textSearch  = $this->generateTextSearchFromItem($item, $lang, $nameForLang, $description);

            $rows[] = [
                'product_id'           => $productId,
                'language'             => $lang,
                'name'                 => $nameForLang,
                'slug'                 => $existingSlugs[$lang] ?? $this->uniqueSlug($baseSlug . '-' . $lang, 'product_translations'),
                'description'          => $description,
                'features'             => $features,
                'specifications'       => $features,
                'short_description'    => $shortDesc,
                'meta_title'           => $nameForLang,
                'meta_description'     => null,
                'outstanding_features' => null,
                'text_search'          => $textSearch,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        DB::table('product_translations')->upsert(
            $rows,
            ['product_id', 'language'],
            [
                'name',
                'description',
                'features',
                'specifications',
                'short_description',
                'meta_title',
                'text_search',
                'updated_at',
            ]
        );
    }

    /**
     * Nối khối đặc điểm (model.characteristic) vào cuối phần mô tả (overview).
     */
    private function appendCharacteristicBlockToDescription(string $description, array $modelPayload, string $lang): string
    {
        if (empty($modelPayload['characteristic']) || !is_array($modelPayload['characteristic'])) {
            return $description;
        }

        $items = [];
        foreach ($modelPayload['characteristic'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            if ($lang === 'en') {
                $line = trim((string) ($row['description_en'] ?? ''));
                if ($line === '') {
                    $line = trim((string) ($row['description'] ?? ''));
                }
            } else {
                $line = trim((string) ($row['description'] ?? ''));
            }
            if ($line !== '') {
                $items[] = $line;
            }
        }

        if ($items === []) {
            return $description;
        }

        $title = $lang === 'en' ? 'Characteristics' : 'Đặc điểm';
        $html  = '<div class="product-model-characteristics mt-4">'
            . '<h4 class="fs-16 font-hanzel text-uppercase fw-500 mb-3">'
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
            . '</h4><ul class="list-unstyled ps-0">';
        foreach ($items as $line) {
            $html .= '<li class="mb-2">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul></div>';

        return $description . $html;
    }

    /**
     * Gán thuộc tính characteristic từ model.characteristic (nhiều dòng, vi/en).
     */
    /**
     * Gán thuộc tính characteristic từ model.characteristic
     * Mỗi dòng characteristic = 1 attribute value riêng.
     */
    private function assignModelCharacteristicAttribute(int $productId, array $item, string $categoryCode): void
    {
        $rows = $this->extractCharacteristicAttributeRows($item);

        if ($rows === []) {
            return;
        }

        foreach ($rows as $row) {
            $valueVi = trim((string) ($row['vi'] ?? ''));
            $valueEn = trim((string) ($row['en'] ?? ''));

            if ($valueVi === '') {
                continue;
            }

            $this->assignAttributeValue(
                $productId,
                'characteristic',
                $valueVi,
                $categoryCode,
                $valueEn !== '' ? $valueEn : $valueVi
            );
        }
    }

    /**
     * @return array<int, array{vi: string, en: string}>
     */
    private function extractCharacteristicAttributeRows(array $item): array
    {
        $model = $item['model'] ?? null;
        if (!is_array($model) || empty($model['characteristic']) || !is_array($model['characteristic'])) {
            return [];
        }

        $rows = [];
        $seen = [];

        foreach ($model['characteristic'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $vi = trim((string) ($row['description'] ?? ''));
            $en = trim((string) ($row['description_en'] ?? ''));

            if ($vi === '') {
                continue;
            }

            if ($en === '') {
                $en = $vi;
            }

            // chống trùng trong cùng 1 sản phẩm import
            $dedupeKey = mb_strtolower($vi, 'UTF-8');
            if (isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;

            $rows[] = [
                'vi' => $vi,
                'en' => $en,
            ];
        }

        return $rows;
    }
    private function buildShortDescription(array $item): string
    {
        $parts = [];
        $size  = trim((string) ($item['size'] ?? ''));
        if ($size !== '') {
            $parts[] = $size;
        }
        $tread = trim((string) ($item['tread'] ?? ''));
        if ($tread !== '') {
            $parts[] = $tread;
        }
        if (empty($parts)) {
            return trim((string) ($item['item_name'] ?? ''));
        }
        return implode(' - ', $parts);
    }

    /**
     * Tạo chuỗi text_search từ item API (quy cách, mẫu xe, tên, mã...).
     */
    private function generateTextSearchFromItem(array $item, string $language, string $name, string $description): string
    {
        $terms = [];

        // Thông tin cơ bản
        $terms[] = $name;
        $terms[] = trim((string) ($item['item_no'] ?? ''));

        // Tất cả các field trong ATTRIBUTE_MAP → lưu dạng "code:value"
        foreach (self::ATTRIBUTE_MAP as $apiField => $attributeCode) {
            $raw = $item[$apiField] ?? '';
            if (is_array($raw)) {
                if ($apiField === 'model') {
                    $raw = (string) (($raw['name'] ?? '') ?: ($raw['code'] ?? ''));
                } else {
                    $raw = '';
                }
            }
            $value = trim((string) $raw);
            if ($value === '' || $value === '-') {
                continue;
            }
            // Tránh trùng lặp nếu nhiều apiField map về cùng 1 attributeCode (vd: wide/width → wide)
            $key = $attributeCode . ':' . $value . " | ";
            if (!in_array($key, $terms, true)) {
                $terms[] = $key;
            }
            // Thêm value thuần để tìm kiếm không cần prefix
            $terms[] = $value;
        }

        // Vehicle fitment cho text_search: chỉ lưu cụm đầy đủ
        // Ví dụ: "Honda city 2001-2009", "Kia morning 2001-2009"
        $fitmentItems = [];

        if (is_array($item['sub'] ?? null)) {
            $fitmentItems = array_merge($fitmentItems, $item['sub']);
        }

        if (is_array($item['car'] ?? null)) {
            $fitmentItems = array_merge($fitmentItems, $item['car']);
        }

        if ($fitmentItems !== []) {
            $vehiclePhrases = [];
            $seenVehicles   = [];

            foreach ($fitmentItems as $fitment) {
                if (!is_array($fitment)) {
                    continue;
                }

                $manufacturer = trim((string) ($fitment['automaker'] ?? $fitment['manufacturer'] ?? ''));
                $model        = trim((string) ($fitment['model'] ?? ''));
                $year         = trim((string) ($fitment['year_production'] ?? ''));

                $manufacturer = ($manufacturer === '-' ? '' : $manufacturer);
                $model        = ($model === '-' ? '' : $model);
                $year         = ($year === '-' ? '' : $year);

                $phraseParts = array_values(array_filter([$manufacturer, $model, $year], fn($v) => $v !== ''));
                if ($phraseParts === []) {
                    continue;
                }

                $phrase = implode(' ', $phraseParts);
                $dedupeKey = mb_strtolower($phrase, 'UTF-8');

                if (!isset($seenVehicles[$dedupeKey])) {
                    $seenVehicles[$dedupeKey] = true;
                    $vehiclePhrases[] = $phrase;
                    $terms[] = $phrase;
                }
            }

            if ($vehiclePhrases !== []) {
                $terms[] = implode(', ', $vehiclePhrases);
            }
        }

        if ($description !== '') {
            $terms[] = strip_tags($description);
        }

        $modelPayload = is_array($item['model'] ?? null) ? $item['model'] : null;
        if ($modelPayload && !empty($modelPayload['characteristic']) && is_array($modelPayload['characteristic'])) {
            foreach ($modelPayload['characteristic'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $t = $language === 'en'
                    ? trim((string) ($row['description_en'] ?? '')) ?: trim((string) ($row['description'] ?? ''))
                    : trim((string) ($row['description'] ?? ''));
                if ($t !== '') {
                    $terms[] = $t;
                }
            }
        }

        // Loại bỏ trùng lặp, nối lại
        $terms = array_values(array_unique(array_filter($terms)));
        $text  = implode(' ', $terms);
        $text  = mb_strtolower($text, 'UTF-8');
        $text  = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }



    /**
     * Chuẩn hoá mã danh mục con về dạng đầy đủ `parent_child`.
     * Ví dụ:
     * - parent=04, raw=2104     => 04_2104
     * - parent=04, raw=04_2104  => 04_2104
     */
    private function normalizeChildCategoryCode(string $parentCode, string $rawChildCode): string
    {
        $rawChildCode = trim($rawChildCode);
        if ($rawChildCode === '' || $rawChildCode === '0') {
            return '';
        }

        if (str_contains($rawChildCode, '_')) {
            return $rawChildCode;
        }

        return $parentCode . '_' . $rawChildCode;
    }



    private function extractModelCategoryCode(array $item): string
    {
        $model = $item['model'] ?? null;
        $modelCode = is_array($model) ? trim((string) ($model['code'] ?? '')) : '';
        if ($modelCode !== '' && $modelCode !== '0') {
            return $modelCode;
        }

        return '';
    }


    private function resolveCategoryHierarchyCodes(array $item, string $categoryCode): array
    {
        $parentCode = trim((string) ($item['item_category_code'] ?? $categoryCode));
        if ($parentCode === '') {
            $parentCode = $categoryCode;
        }

        // Quy ước mới:
        // - item_category_code: "03"
        // - item_group_code: "0302"  => child = "03_0302"
        // - model.code: "0101"       => model = "03_0302_0101" (nếu có group) hoặc "03_0101" (nếu không có group)
        $groupCode = trim((string) ($item['item_group_code'] ?? ''));
        $modelCodeRaw = $this->extractModelCategoryCode($item);

        // Case: item_group_code null nhưng model.code có => chỉ 1 danh mục: 03_0101
        if ($groupCode === '' && $modelCodeRaw !== '') {
            $base = $parentCode !== '' ? $parentCode : $categoryCode;
            return array_values(array_unique(array_filter([
                $base !== '' ? ($base . '_' . $modelCodeRaw) : '',
            ])));
        }

        $codes = [];
        if ($parentCode !== '') {
            $codes[] = $parentCode;
        }

        // Case: có group => 2 hoặc 3 danh mục
        if ($groupCode !== '') {
            $base = $parentCode !== '' ? $parentCode : $categoryCode;
            $childCode = $base !== '' ? ($base . '_' . $groupCode) : '';
            if ($childCode !== '') {
                $codes[] = $childCode;
                if ($modelCodeRaw !== '') {
                    $codes[] = $childCode . '_' . $modelCodeRaw;
                }
            }
        }

        return array_values(array_unique(array_filter($codes)));
    }

    private function attachCategories(int $productId, string $categoryCode, array $item): ?int
    {
        $codes = $this->resolveCategoryHierarchyCodes($item, $categoryCode);
        if ($codes === []) {
            return null;
        }

        $parentCode = $codes[0] ?? '';
        $childCode  = $codes[1] ?? '';
        $modelCode  = $codes[2] ?? '';

        // Quy ước mới: có thể chỉ có 1 code dạng "03_0101" (không có group).
        // UI/Vehicle search cần sản phẩm có liên kết với danh mục cha ("03"/"04") để filter theo loại xe.
        // Vì vậy luôn attach cả cha + con, nhưng giữ "con" làm primary để không đổi hành vi hiển thị danh mục chính.
        $attachParent = true;
        if ($childCode === '' && $modelCode === '' && $parentCode !== '' && str_contains($parentCode, '_')) {
            $derivedParent = explode('_', $parentCode, 2)[0] ?? '';
            if ($derivedParent !== '') {
                $childCode = $parentCode;
                $parentCode = $derivedParent;
            }
        }

        $parentId = null;
        $childId  = null;
        $modelId  = null;

        if ($parentCode !== '' && isset($this->categoryCodeMap[$parentCode])) {
            $parentId = $this->categoryCodeMap[$parentCode];
        } else {
            Log::channel('import_external')->warning('[Product] Gán danh mục: code cha không có trong map', [
                'product_id' => $productId,
                'code'       => $parentCode,
                'item_no'    => $item['item_no'] ?? null,
            ]);
        }

        if ($childCode !== '' && $parentCode !== '') {
            $childId = $this->resolveOrCreateChildCategoryId($parentCode, $childCode, $item);
        }

        if ($modelCode !== '' && $childCode !== '') {
            $modelId = $this->resolveOrCreateChildCategoryId($childCode, $modelCode, $item);
        }

        $orderedCatIds = array_values(array_unique(array_filter([
            $modelId,
            $childId,
            $attachParent ? $parentId : null,
        ])));

        $this->attachCategoryIds($productId, $orderedCatIds);

        return $orderedCatIds[0] ?? null;
    }

    /**
     * Resolve danh mục con: thử nhiều key (04_0401, 04_401). Nếu không có trong map nhưng cha "04" có thì tạo danh mục con.
     */
    private function resolveOrCreateChildCategoryId(string $parentCode, string $resolvedChildCode, array $item): ?int
    {
        $normalizedCode = $this->normalizeChildCategoryCode($parentCode, $resolvedChildCode);
        if ($normalizedCode === '') {
            return null;
        }

        $keysToTry = [$normalizedCode];

        $suffix = str_contains($normalizedCode, '_')
            ? Str::afterLast($normalizedCode, '_')
            : $normalizedCode;

        if (ctype_digit($suffix) && strlen($suffix) < 4) {
            $keysToTry[] = $parentCode . '_' . str_pad($suffix, 4, '0', STR_PAD_LEFT);
        }

        foreach (array_unique($keysToTry) as $key) {
            if (isset($this->categoryCodeMap[$key])) {
                return $this->categoryCodeMap[$key];
            }
        }

        $parentId = $this->categoryCodeMap[$parentCode] ?? null;
        if ($parentId === null) {
            Log::channel('import_external')->debug('[Product] Bỏ qua danh mục con: cha không có trong map', [
                'parent_code' => $parentCode,
                'child_code'  => $normalizedCode,
            ]);
            return null;
        }

        $displayName = trim((string) ($item['item_model_name'] ?? ''));
        if ($displayName === '') {
            $displayName = trim((string) ($item['item_group_name'] ?? ''));
        }
        if ($displayName === '') {
            $displayName = $suffix;
        }

        $childId = $this->createChildCategoryIfMissing($parentId, $normalizedCode, $displayName, $item);
        if ($childId !== null) {
            $this->categoryCodeMap[$normalizedCode] = $childId;
        }
        return $childId;
    }

    /**
     * Tạo danh mục con trong DB nếu chưa tồn tại.
     * Mặc định bị chặn; chỉ chạy khi setAllowCategoryAutoCreate(true).
     */
    private function createChildCategoryIfMissing(int $parentId, string $code, string $displayName, array $item): ?int
    {
        if (!Schema::hasTable('product_categories')) {
            return null;
        }

        $existing = DB::table('product_categories')->where('code', $code)->first();
        if ($existing) {
            return (int) $existing->id;
        }

        if (!$this->shouldAutoCreateCategories()) {
            Log::channel('import_external')->warning('[Category] Chặn auto-create category do cấu hình hiện tại', [
                'code'      => $code,
                'parent_id' => $parentId,
                'item_no'   => $item['item_no'] ?? null,
                'app_env'   => app()->environment(),
                'allow_auto_create' => $this->allowCategoryAutoCreate,
            ]);
            return null;
        }

        $name = 'Danh mục ' . $displayName;
        $categoryId = DB::table('product_categories')->insertGetId([
            'parent_id'   => $parentId,
            'code'        => $code,
            'is_active'   => true,
            'is_featured' => false,
            'sort_order'  => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        if (Schema::hasTable('product_category_translations')) {
            foreach (['vi', 'en'] as $lang) {
                $slug = Str::slug($name) . '-' . $code . '-' . $lang;
                $slug = $this->uniqueSlugForCategory($slug, $categoryId, $lang);
                DB::table('product_category_translations')->insert([
                    'category_id' => $categoryId,
                    'language'    => $lang,
                    'name'        => $name,
                    'slug'        => $slug,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
        Log::channel('import_external')->info('[Product] Tạo danh mục con (API không trả về sub)', [
            'code'      => $code,
            'parent_id' => $parentId,
            'item_no'   => $item['item_no'] ?? null,
        ]);
        return $categoryId;
    }

    private function uniqueSlugForCategory(string $base, int $categoryId, string $lang): string
    {
        $slug   = $base ?: 'danh-muc';
        $count  = 1;
        $table  = 'product_category_translations';
        while (DB::table($table)->where('slug', $slug)->where('language', $lang)->where('category_id', '!=', $categoryId)->exists()) {
            $slug = $base . '-' . $count++;
        }
        return $slug;
    }

    private function attachCategoryIds(int $productId, array $catIds): void
    {
        $catIds = array_values(array_unique(array_map('intval', $catIds)));
        if ($catIds === []) {
            return;
        }

        $now  = now();
        $rows = [];

        foreach ($catIds as $idx => $catId) {
            $isPrimary = $idx === 0;

            $rows[] = [
                'product_id'          => $productId,
                'product_category_id' => $catId,
                'is_primary'          => $isPrimary,
                'sort_order'          => $isPrimary ? 0 : $idx,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        DB::table('product_product_category')
            ->where('product_id', $productId)
            ->whereNotIn('product_category_id', $catIds)
            ->delete();

        DB::table('product_product_category')->upsert(
            $rows,
            ['product_id', 'product_category_id'],
            ['is_primary', 'sort_order', 'updated_at']
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Attributes
    // ─────────────────────────────────────────────────────────────────────────

    private function assignAttributeValue(
        int    $productId,
        string $attributeCode,
        string $value,
        string $categoryCode,
        string $valueEn = ''
    ): void {
        $value = trim($value);

        if ($value === '' || Str::startsWith($attributeCode, 'picture')) {
            return;
        }

        $attributeId      = $this->findOrCreateAttribute($attributeCode);
        $attributeValueId = $this->getOrCreateAttributeValueId(
            $attributeId,
            $attributeCode,
            $value,
            $categoryCode,
            $valueEn
        );

        $this->currentProductAttributeValueIds[$attributeValueId] = true;

        DB::table('product_attribute_product')->upsert(
            [[
                'product_id'         => $productId,
                'attribute_value_id' => $attributeValueId,
                'show_detail'        => $this->getShowDetailForAttribute($categoryCode, $attributeCode),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]],
            ['product_id', 'attribute_value_id'],
            ['show_detail', 'updated_at']
        );
    }

    private function getOrCreateAttributeValueId(
        int $attributeId,
        string $attributeCode,
        string $value,
        string $categoryCode,
        string $valueEn = ''
    ): int {
        $cacheKey = $attributeId . ':' . $value;

        if (isset($this->attributeValueCache[$cacheKey])) {
            return $this->attributeValueCache[$cacheKey];
        }

        $existing = DB::table('product_attribute_values')
            ->where('attribute_id', $attributeId)
            ->where('value', $value)
            ->first();

        if ($existing) {
            $attributeValueId = (int) $existing->id;
        } else {
            $attributeValueId = DB::table('product_attribute_values')->insertGetId([
                'attribute_id' => $attributeId,
                'value'        => $value,
                'vehicle_type' => $this->resolveVehicleType($categoryCode, $attributeCode),
                'is_active'    => true,
                'sort_order'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        DB::table('product_attribute_value_translations')->upsert(
            [
                [
                    'attribute_value_id' => $attributeValueId,
                    'language'           => 'vi',
                    'value'              => $value,
                ],
                [
                    'attribute_value_id' => $attributeValueId,
                    'language'           => 'en',
                    'value'              => $valueEn !== '' ? $valueEn : $value,
                ],
            ],
            ['attribute_value_id', 'language'],
            ['value']
        );

        $this->attributeValueCache[$cacheKey] = $attributeValueId;

        return $attributeValueId;
    }

    /**
     * Thuộc tính nào được hiện ở chi tiết sản phẩm (theo mã danh mục 01, 03, 04).
     */
    private function getShowDetailForAttribute(string $categoryCode, string $attributeCode): string
    {
        $showDetail = in_array($attributeCode, self::HIDDEN_ATTRIBUTE_CODES, true) ? 'N' : 'Y';
        return $showDetail;
    }

    private function findOrCreateAttribute(string $code): int
    {
        if (isset($this->attributeCache[$code])) {
            return $this->attributeCache[$code];
        }

        $row = DB::table('product_attributes')->where('code', $code)->first();

        if ($row) {
            $this->attributeCache[$code] = $row->id;
            return $row->id;
        }

        $id = DB::table('product_attributes')->insertGetId([
            'code'          => $code,
            'type'          => $this->attributeType($code),
            'is_required'   => false,
            'is_filterable' => true,
            'is_comparable' => true,
            'is_active'     => true,
            'sort_order'    => 1,
            'options'       => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        foreach (['vi', 'en'] as $lang) {
            DB::table('product_attribute_translations')->insert([
                'attribute_id' => $id,
                'language'     => $lang,
                'name'         => $this->attributeName($code, $lang),
                'description'  => null,
            ]);
        }

        $this->attributeCache[$code] = $id;
        return $id;
    }

	// ─────────────────────────────────────────────────────────────────────────
	// Vehicle fitments
	// ─────────────────────────────────────────────────────────────────────────

    /**
     * Phân tách year_production thành danh sách năm riêng lẻ.
     * Hỗ trợ các biến thể phổ biến như:
     * - 2019-2024
     * - 2019 - 2024
     * - 2019–2024
     * - 2019 — 2024
     * - 2019
     *
     * Nếu dữ liệu không parse được về năm hợp lệ thì fallback về [null]
     * để tránh đẩy chuỗi rác vào cột year trong DB.
     *
     * @param mixed $yearProduction
     * @return array<int, int|null>
     */
    private function expandYearProduction(mixed $yearProduction): array
    {
        $rawOriginal = trim((string) $yearProduction);

        if ($rawOriginal === '' || $rawOriginal === '-') {
            return [null];
        }

        $raw = preg_replace('/[–—−‑‒﹘﹣－]/u', '-', $rawOriginal);
        $raw = preg_replace('/\s*-\s*/u', '-', (string) $raw);
        $raw = preg_replace('/\s+/u', ' ', trim((string) $raw));

        if ($raw === '' || $raw === '-') {
            return [null];
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', $raw, $m)) {
            $start = (int) $m[1];
            $end   = (int) $m[2];

            if ($start > $end) {
                [$start, $end] = [$end, $start];
            }

            return range($start, $end);
        }

        if (preg_match('/^\d{4}$/', $raw)) {
            return [(int) $raw];
        }

        if (preg_match_all('/(\d{4})/u', $raw, $matches) >= 1) {
            $years = array_values(array_unique(array_map('intval', $matches[1])));
            sort($years);

            if (count($years) === 1) {
                return [$years[0]];
            }

            if (count($years) >= 2) {
                return range($years[0], $years[1]);
            }
        }

        Log::channel('import_external')->warning('[Product] year_production không hợp lệ, fallback null', [
            'year_production_raw' => $rawOriginal,
            'year_production_normalized' => $raw,
        ]);

        return [null];
    }


    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function isValid(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '-') {
            return false;
        }
        if (is_array($value) && empty($value)) {
            return false;
        }
        return true;
    }

    private function extractImages(array $item): array
    {
        $images = [];

        // Payload mới: picture: { "1": "...", "2": "", ... }
        $picture = $item['picture'] ?? null;
        if (is_array($picture)) {
            foreach (['1', '2', '3', '4', '5'] as $idx) {
                $url = trim((string) ($picture[$idx] ?? ''));
                if ($url !== '') {
                    $images[] = $url;
                }
            }
        }

        // Backward-compatible: picture1..picture5
        if ($images === []) {
            foreach (['picture1', 'picture2', 'picture3', 'picture4', 'picture5'] as $key) {
                $url = trim((string) ($item[$key] ?? ''));
                if ($url !== '') {
                    $images[] = $url;
                }
            }
        }

        return $images;
    }

    private function uniqueSku(string $base): string
    {
        $sku     = $base;
        $counter = 2;
        while (DB::table('products')->where('sku', $sku)->exists()) {
            $sku = $base . '-' . $counter++;
        }
        return $sku;
    }

    private function uniqueSlug(string $base, string $table): string
    {
        $slug    = $base ?: 'san-pham';
        $counter = 1;
        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }

    private function resolveVehicleType(string $categoryCode, string $attributeCode): string
    {
        if ($attributeCode !== 'model') {
            return 'all';
        }
        return match ($categoryCode) {
            '03'    => 'xe-may',
            '04'    => 'oto',
            default => 'all',
        };
    }

    private function ensureCategoryCodeMapLoaded(): void
    {
        if ($this->categoryCodeMap !== []) {
            return;
        }
        $this->categoryCodeMap = $this->loadCategoryCodeMap();
        Log::channel('import_external')->info('[Product] Đã load category code map từ DB', [
            'count' => count($this->categoryCodeMap),
            'codes' => array_keys($this->categoryCodeMap),
        ]);
    }

    private function loadCategoryCodeMap(): array
    {
        if (!Schema::hasTable('product_categories')) {
            return [];
        }

        $map = [];
        $rows = DB::table('product_categories')->select('id', 'code')->get();
        foreach ($rows as $row) {
            $map[$row->code] = $row->id;
        }

        // Alias numeric codes cho các root category đã được đổi tên thành slug.
        // Khi import lại sau khi đã rename, API vẫn trả về "04", "01", "03"
        // nhưng DB đã có code "lop-advenza-pcr", "sam-lop-xe-tai", "sam-lop-xe-may".
        $numericAliases = [
            '04' => 'lop-advenza-pcr',
            '01' => 'sam-lop-xe-tai',
            '03' => 'sam-lop-xe-may',
        ];
        foreach ($numericAliases as $numericCode => $slug) {
            if (!isset($map[$numericCode]) && isset($map[$slug])) {
                $map[$numericCode] = $map[$slug];
            }
        }

        return $map;
    }

    private function attributeType(string $code): string
    {
        return match ($code) {
            'characteristic' => 'text',
            'wide', 'rate', 'diameter', 'ply_rating', 'load_index_number',
            'outer_diameter', 'body_thickness', 'folded_length' => 'number',
            'tire_type', 'speed_index', 'speed_rating'          => 'select',
            default                                              => 'text',
        };
    }

    /**
     * Đảm bảo thuộc tính size (text) được gán: nếu API không gửi 'size' thì build từ wide + rate + diameter.
     * - size (text): chuỗi hiển thị, vd "195/65R15" hoặc "7.50-16"
     * - wide (number), rate (number), diameter (number): đã gán trong ATTRIBUTE_MAP
     * Format build: "wide/rateR diameter" (vd: 195/65R15) hoặc "wide-diameter" (vd: 7.50-16) nếu không có rate.
     */
    private function ensureSizeAttributeFromWideRateDiameter(int $productId, array $item, string $categoryCode): void
    {
        $sizeFromApi = trim((string) ($item['size'] ?? ''));
        if ($sizeFromApi !== '') {
            return; // API đã gửi size, đã được gán trong loop ATTRIBUTE_MAP
        }

        $wide     = trim((string) ($item['wide'] ?? $item['width'] ?? ''));
        $rate     = trim((string) ($item['rate'] ?? ''));
        $diameter = trim((string) ($item['wheel_diameter'] ?? $item['diameter'] ?? ''));

        if ($wide === '' && $rate === '' && $diameter === '') {
            return;
        }

        if ($rate !== '' && $diameter !== '') {
            $size = ($wide !== '' ? $wide . '/' : '') . $rate . 'R' . $diameter;
        } elseif ($diameter !== '') {
            $size = $wide !== '' ? $wide . '-' . $diameter : $diameter;
        } else {
            $size = $wide ?: $rate;
        }
        $size = trim($size, " \t\n\r\0\x0B/-R");
        if ($size !== '') {
            $this->assignAttributeValue($productId, 'size', $size, $categoryCode);
        }
    }

    /**
     * Nếu sản phẩm có Size (vd. "175/65R14") nhưng thiếu wide/rate/diameter → parse và gán thuộc tính.
     * Chiều rộng (wide): số đầu trong "175/65R14" = 175; trong "7.50-16" = 7.50.
     */
    private function ensureWideRateDiameterFromSize(int $productId, array $item, string $categoryCode): void
    {
        $sizeRaw = $item['size'] ?? $item['Size'] ?? '';
        $sizeStr = is_numeric($sizeRaw) ? (string) (int) $sizeRaw : trim((string) $sizeRaw);
        if ($sizeStr === '') {
            return;
        }

        $parsed = $this->parseTireSize($sizeStr);
        if (empty($parsed)) {
            return;
        }

        $hasWide   = $this->isValid($item['wide'] ?? $item['width'] ?? $item['Chiều rộng'] ?? '');
        $hasRate   = $this->isValid($item['rate'] ?? $item['Tỉ lệ'] ?? '');
        $hasDiameter = $this->isValid($item['wheel_diameter'] ?? $item['diameter'] ?? $item['Đường kính mâm'] ?? '');

        if (!empty($parsed['wide']) && !$hasWide) {
            $this->assignAttributeValue($productId, 'wide', $parsed['wide'], $categoryCode);
        }
        if (!empty($parsed['rate']) && !$hasRate) {
            $this->assignAttributeValue($productId, 'rate', $parsed['rate'], $categoryCode);
        }
        if (!empty($parsed['diameter']) && !$hasDiameter) {
            $this->assignAttributeValue($productId, 'diameter', $parsed['diameter'], $categoryCode);
        }
    }

    /**
     * Parse chuỗi size lốp thành wide, rate, diameter.
     * - "175/65R14" hoặc "175/65 R14" → wide=175, rate=65, diameter=14
     * - "7.50-16" → wide=7.50, diameter=16 (rate rỗng)
     *
     * @return array{wide?: string, rate?: string, diameter?: string}
     */
    private function parseTireSize(string $size): array
    {
        $size = trim(preg_replace('/\s+/', '', $size));
        if ($size === '') {
            return [];
        }

        // Dạng PCR: 175/65R14, 185/55R15, 195/65R15
        if (preg_match('/^(\d+(?:\.\d+)?)\/(\d+)R(\d+)$/i', $size, $m)) {
            return [
                'wide'    => $m[1],
                'rate'    => $m[2],
                'diameter' => $m[3],
            ];
        }

        // Dạng bias: 7.50-16, 8.25-16
        if (preg_match('/^(\d+(?:\.\d+)?)-(\d+)$/', $size, $m)) {
            return [
                'wide'    => $m[1],
                'diameter' => $m[2],
            ];
        }

        return [];
    }

    private function attributeName(string $code, string $lang): string
    {
        $names = [
            'tread'             => ['vi' => 'Hoa lốp',            'en' => 'Tread Pattern'],
            'wide'              => ['vi' => 'Chiều rộng',          'en' => 'Width'],
            'rate'              => ['vi' => 'Tỷ lệ',               'en' => 'Aspect Ratio'],
            'diameter'          => ['vi' => 'Đường kính',          'en' => 'Diameter'],
            'ply_rating'        => ['vi' => 'Số lớp bố',           'en' => 'Ply Rating'],
            'tire_type'         => ['vi' => 'Loại lốp',            'en' => 'Tire Type'],
            'load_index_number' => ['vi' => 'Chỉ số tải',          'en' => 'Load Index'],
            'speed_index'       => ['vi' => 'Chỉ số tốc độ',       'en' => 'Speed Index'],
            'tread_depth'       => ['vi' => 'Chiều sâu gai (mm)',  'en' => 'Tread Depth (mm)'],
            'warranty'          => ['vi' => 'Bảo hành',            'en' => 'Warranty'],
            'size'              => ['vi' => 'Kích thước',           'en' => 'Size'],
            'road_grip'         => ['vi' => 'Bám đường',           'en' => 'Road Grip'],
            'heat_resistance'   => ['vi' => 'Chịu nhiệt',          'en' => 'Heat Resistance'],
            'etrto'             => ['vi' => 'Mã ETRTO',             'en' => 'ETRTO Code'],
            'size_inches'       => ['vi' => 'Kích thước (Inches)',  'en' => 'Size (Inches)'],
            'outer_diameter'    => ['vi' => 'Đường kính ngoài',    'en' => 'Outer Diameter'],
            'rim_diameter'      => ['vi' => 'Đường kính vành',     'en' => 'Rim Diameter'],
            'sidewall_width'    => ['vi' => 'Chiều rộng hông',     'en' => 'Sidewall Width'],
            'rim_width'         => ['vi' => 'Chiều rộng vành',     'en' => 'Rim Width'],
            'standard_pressure' => ['vi' => 'Nội áp (kPa)',        'en' => 'Pressure (kPa)'],
            'valve'             => ['vi' => 'Van',                  'en' => 'Valve'],
            'body_thickness'    => ['vi' => 'Độ dày thân (mm)',    'en' => 'Body Thickness (mm)'],
            'folded_length'     => ['vi' => 'Chiều dài gập (mm)',  'en' => 'Folded Length (mm)'],

            // --- CÁC TRƯỜNG BỔ SUNG MỚI ---
            'finger'            => ['vi' => 'Số ngón (Finger)',    'en' => 'Finger'],
            'manufacturer'      => ['vi' => 'Nhà sản xuất',        'en' => 'Manufacturer'],
            'model'             => ['vi' => 'Dòng sản phẩm',       'en' => 'Model'],
            'production_type'   => ['vi' => 'Loại hình sản xuất',  'en' => 'Production Type'],
            'weight'            => ['vi' => 'Trọng lượng (kg)',    'en' => 'Weight (kg)'],
            'speed_rating'      => ['vi' => 'Cấp tốc độ',          'en' => 'Speed Rating'],
            'production_year'   => ['vi' => 'Năm sản xuất',        'en' => 'Production Year'],
            'product_features'  => ['vi' => 'Đặc tính sản phẩm',   'en' => 'Product Features'],
            'characteristic'    => ['vi' => 'Đặc điểm',            'en' => 'Characteristics'],
            'tire_line'         => ['vi' => 'Dòng lốp',            'en' => 'Tire Line'],
            'tire_pattern'      => ['vi' => 'Mã hoa lốp',          'en' => 'Tire Pattern Code'],
            'tire_color_group'  => ['vi' => 'Nhóm màu lốp',        'en' => 'Tire Color Group'],
            'etrto_code'        => ['vi' => 'Mã tiêu chuẩn ETRTO', 'en' => 'ETRTO Standard Code'],
        ];

        return $names[$code][$lang] ?? $code;
    }
}
