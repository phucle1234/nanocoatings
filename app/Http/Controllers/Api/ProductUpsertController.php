<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ExternalProductImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductUpsertController extends Controller
{
    private const DEFAULT_CATEGORY_CODE = '04';

    private const CATEGORY_ALIASES = [
        '04' => 'lop-advenza-pcr',
        'lop-advenza-pcr' => '04',
        '01' => 'sam-lop-xe-tai',
        'sam-lop-xe-tai'  => '01',
        '03' => 'sam-lop-xe-may',
        'sam-lop-xe-may'  => '03',
    ];

    public function __construct(
        private readonly ExternalProductImporter $importer,
    ) {}

    /**
     * Create a new product.
     *
     * Business rule:
     * - item_no trong JSON tương ứng với products.sku trong DB.
     * - endpoint này chỉ dùng để tạo mới, không được update đè sản phẩm đã có.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateStorePayload($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $item = $request->all();
        $sku  = $this->extractSkuFromItem($item);

        if (DB::table('products')->where('sku', $sku)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm SKU \"{$sku}\" đã tồn tại. Hãy dùng endpoint update-by-sku.",
                'errors'  => [
                    'item_no' => ["Sản phẩm SKU \"{$sku}\" đã tồn tại."],
                ],
            ], 409);
        }

        return $this->importSingleItem($item, $sku, expectedAction: 'created', successStatus: 201);
    }

    /**
     * Update an existing product by SKU.
     *
     * Route expected:
     * POST /products/update-by-sku
     *
     * Business rule:
     * - item_no trong JSON chính là SKU trong DB.
     * - nếu payload có thêm field sku thì phải khớp với item_no.
     */
    public function updateBySku(Request $request): JsonResponse
    {
        $validator = $this->validateUpdatePayload($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $item = $request->all();
        $sku  = $this->extractSkuFromItem($item);

        $existing = DB::table('products')
            ->where('sku', $sku)
            ->select('id', 'code', 'sku')
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy sản phẩm với SKU \"{$sku}\".",
            ], 404);
        }

        // Chuẩn hóa để importer luôn nhận item_no là SKU.
        $item['item_no'] = $sku;

        return $this->importSingleItem($item, $sku, expectedAction: 'updated', successStatus: 200);
    }

    public function updatePrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_no'    => ['required', 'string', 'max:64'],
            'price'      => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'sku'        => ['nullable', 'string', 'max:64'],
        ], [
            'item_no.required' => 'SKU sản phẩm (item_no) là bắt buộc.',
            'price.required'   => 'Giá bán là bắt buộc.',
        ])->after(function ($validator) use ($request) {
            $itemNo = trim((string) $request->input('item_no', ''));
            $sku    = trim((string) $request->input('sku', ''));

            if ($itemNo !== '' && $sku !== '' && $itemNo !== $sku) {
                $validator->errors()->add(
                    'sku',
                    "sku trong payload ({$sku}) không khớp với item_no ({$itemNo})."
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $sku = trim((string) $request->input('item_no'));

        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy sản phẩm với SKU \"{$sku}\".",
            ], 404);
        }

        $product->update([
            'price'      => $request->price,
            'sale_price' => $request->sale_price ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Giá sản phẩm đã được cập nhật.',
            'product' => $product->fresh(),
        ], 200);
    }

    private function importSingleItem(array $item, string $sku, string $expectedAction, int $successStatus): JsonResponse
    {
        $categoryCode = $this->resolveCategoryCode($item);

        $categoryError = $this->validateCategory($categoryCode, $item);
        if ($categoryError !== null) {
            return $categoryError;
        }

        try {
            DB::beginTransaction();

            // Chuẩn hóa trước khi đẩy vào importer.
            $item['item_no'] = $sku;

            $stats = $this->importer->import([$item], $categoryCode);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::channel('import_external')->error('[API] Upsert sản phẩm thất bại', [
                'sku'   => $sku,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import thất bại: ' . $e->getMessage(),
            ], 500);
        }

        if (($stats['skipped'] ?? 0) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm bị bỏ qua. Kiểm tra item_no và item_name.',
                'stats'   => $stats,
            ], 422);
        }

        $actualAction = ($stats['created'] ?? 0) > 0 ? 'created' : 'updated';

        if ($expectedAction !== $actualAction) {
            return response()->json([
                'success' => false,
                'message' => $expectedAction === 'created'
                    ? 'Endpoint create chỉ dùng để tạo mới, nhưng dữ liệu hiện tại lại dẫn tới cập nhật.'
                    : 'Endpoint update chỉ dùng để cập nhật, nhưng dữ liệu hiện tại lại dẫn tới tạo mới.',
                'stats'   => $stats,
            ], 409);
        }

        $product = $this->findProductSummaryBySku($sku);

        return response()->json([
            'success' => true,
            'action'  => $actualAction,
            'message' => $actualAction === 'created'
                ? 'Sản phẩm đã được tạo mới.'
                : 'Sản phẩm đã được cập nhật.',
            'stats'   => $stats,
        ], $successStatus);
    }

    private function validateStorePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'item_no'   => ['required', 'string', 'max:64'],
            'item_name' => ['required', 'string', 'max:512'],
            'sku'       => ['nullable', 'string', 'max:64'],
        ], [
            'item_no.required'   => 'SKU sản phẩm (item_no) là bắt buộc.',
            'item_name.required' => 'Tên sản phẩm (item_name) là bắt buộc.',
        ])->after(function ($validator) use ($request) {
            $this->validateSkuConsistency($validator, $request);
        });
    }

    private function validateUpdatePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'item_no'   => ['required', 'string', 'max:64'],
            'item_name' => ['required', 'string', 'max:512'],
            'sku'       => ['nullable', 'string', 'max:64'],
        ], [
            'item_no.required'   => 'SKU sản phẩm (item_no) là bắt buộc.',
            'item_name.required' => 'Tên sản phẩm (item_name) là bắt buộc.',
        ])->after(function ($validator) use ($request) {
            $this->validateSkuConsistency($validator, $request);
        });
    }

    private function validateSkuConsistency($validator, Request $request): void
    {
        $itemNo = trim((string) $request->input('item_no', ''));
        $sku    = trim((string) $request->input('sku', ''));

        if ($itemNo !== '' && $sku !== '' && $itemNo !== $sku) {
            $validator->errors()->add(
                'sku',
                "sku trong payload ({$sku}) không khớp với item_no ({$itemNo})."
            );
        }
    }

    private function extractSkuFromItem(array $item): string
    {
        $itemNo = trim((string) ($item['item_no'] ?? ''));
        $sku    = trim((string) ($item['sku'] ?? ''));

        return $itemNo !== '' ? $itemNo : $sku;
    }

    private function findProductSummaryBySku(string $sku): ?object
    {
        return DB::table('products')
            ->where('sku', $sku)
            ->select('id', 'code', 'sku', 'category_id', 'price', 'sale_price', 'image_urls', 'is_bestseller', 'updated_at')
            ->first();
    }

    private function validateCategory(string $categoryCode, array $item): ?JsonResponse
    {
        $parentCategory = DB::table('product_categories')
            ->where('code', $categoryCode)
            ->orWhere('code', self::CATEGORY_ALIASES[$categoryCode] ?? '__none__')
            ->select('id', 'code')
            ->first();

        if (!$parentCategory) {
            Log::channel('import_external')->warning('[API] Danh mục cha không tồn tại', [
                'sku'                => $item['item_no'] ?? ($item['sku'] ?? '?'),
                'item_category_code' => $categoryCode,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Danh mục cha \"{$categoryCode}\" không tồn tại.",
                'errors'  => [
                    'item_category_code' => ["Không tìm thấy danh mục cha \"{$categoryCode}\"."],
                ],
            ], 422);
        }

        $rawChildCode = trim((string) ($item['item_category_code_sub'] ?? ''));

        if ($rawChildCode !== '') {
            $normalizedChildCode = $this->normalizeChildCategoryCode($categoryCode, $rawChildCode);

            $childExists = DB::table('product_categories')
                ->where('parent_id', $parentCategory->id)
                ->where('code', $normalizedChildCode)
                ->exists();

            if (!$childExists) {
                Log::channel('import_external')->warning('[API] Danh mục con không tồn tại hoặc không thuộc danh mục cha', [
                    'sku'                          => $item['item_no'] ?? ($item['sku'] ?? '?'),
                    'item_category_code'           => $categoryCode,
                    'item_category_code_sub'       => $rawChildCode,
                    'normalized_item_category_sub' => $normalizedChildCode,
                    'parent_category_id'           => $parentCategory->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Danh mục con \"{$rawChildCode}\" không tồn tại hoặc không thuộc danh mục cha \"{$categoryCode}\".",
                    'errors'  => [
                        'item_category_code_sub' => [
                            "Không tìm thấy danh mục con \"{$rawChildCode}\" thuộc danh mục cha \"{$categoryCode}\".",
                        ],
                    ],
                ], 422);
            }
        }

        return null;
    }

    private function normalizeChildCategoryCode(string $parentCode, string $rawChildCode): string
    {
        $rawChildCode = trim($rawChildCode);
        if ($rawChildCode === '') {
            return '';
        }

        if (str_contains($rawChildCode, '_')) {
            return $rawChildCode;
        }

        return $parentCode . '_' . $rawChildCode;
    }

    private function resolveCategoryCode(array $item): string
    {
        $categoryCode = trim((string) ($item['item_category_code'] ?? ''));

        return $categoryCode !== '' ? $categoryCode : self::DEFAULT_CATEGORY_CODE;
    }
}
