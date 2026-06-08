<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NppShowroomImporter
{
    /**
     * @param array<int, string> $onlyParentCodes
     * @param array<int, string> $onlyShowroomCodes
     * @return array{success: bool, message: string, data?: array, status?: int, error?: string}
     */
    public function importAll(
        array $onlyParentCodes = [],
        array $onlyShowroomCodes = [],
        bool $dryRun = false
    ): array {
        $baseUrl = rtrim((string) config('services.casumina.url'), '/');

        if ($baseUrl === '') {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình CASUMINA_API_URL',
            ];
        }

        $sourceRows = $this->fetchSourceCodesPayload($baseUrl);

        if ($sourceRows === null) {
            return [
                'success' => false,
                'message' => 'Không gọi được API sourcecodes',
            ];
        }

        if (!empty($onlyParentCodes)) {
            $onlyParentCodes = array_values(array_filter(array_map('strval', $onlyParentCodes)));

            $sourceRows = array_values(array_filter($sourceRows, function ($row) use ($onlyParentCodes) {
                return is_array($row)
                    && in_array((string) ($row['code'] ?? ''), $onlyParentCodes, true);
            }));
        }

        if (empty($sourceRows)) {
            return [
                'success' => false,
                'message' => 'Payload sourcecodes rỗng hoặc không có NPP nào khớp điều kiện lọc',
            ];
        }

        $summary = [
            'dry_run'                           => $dryRun,
            'filtered_parent_codes'             => $onlyParentCodes,
            'filtered_showroom_codes'           => $onlyShowroomCodes,
            'total_npp'                         => count($sourceRows),
            'total_showrooms'                   => $this->countNestedShowrooms($sourceRows, $onlyShowroomCodes),
            'parent_created'                    => 0,
            'parent_updated'                    => 0,
            'showroom_created'                  => 0,
            'showroom_updated'                  => 0,
            'showroom_detail_synced'            => 0,
            'showroom_detail_failed'            => 0,
            'customers_created'                 => 0,
            'customers_updated'                 => 0,
            'customers_skipped'                 => 0,
            'customer_duplicate_code_resolved'  => 0,
            'customer_sync_parents'             => 0,
            'failed'                            => 0,
            'errors'                            => [],
            'customer_import_errors'            => [],
            'customer_cases'                    => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($sourceRows as $parentIndex => $parentRow) {
                try {
                    if (!is_array($parentRow)) {
                        throw new \RuntimeException("NPP record #{$parentIndex} không hợp lệ");
                    }

                    $parentCode      = trim((string) ($parentRow['code'] ?? ''));
                    $parentName      = trim((string) ($parentRow['name'] ?? ''));
                    $parentEmail     = trim((string) ($parentRow['email'] ?? ''));
                    $parentPhone     = trim((string) ($parentRow['phone'] ?? ''));
                    $parentAddress   = trim((string) ($parentRow['address'] ?? ''));
                    $parentCityCode  = trim((string) ($parentRow['city_code'] ?? ''));
                    $parentCityName  = trim((string) ($parentRow['city_name'] ?? ''));
                    $parentCountry   = trim((string) ($parentRow['country'] ?? ''));
                    $parentLatitude  = $this->normalizeNullableNumeric($parentRow['latitude'] ?? null);
                    $parentLongitude = $this->normalizeNullableNumeric($parentRow['longitude'] ?? null);

                    if ($parentCode === '' || $parentName === '') {
                        throw new \RuntimeException('Thiếu code hoặc name của NPP');
                    }

                    [$parentUser, $parentAction] = $this->upsertDealer([
                        'user_name'   => $parentCode,
                        'code'        => $parentCode,
                        'parent_id'   => 0,
                        'parent_code' => null,
                        'source_code' => null,
                        'name'        => $parentName,
                        'password'    => $this->generateDefaultDealerPassword($parentCode),
                        'email'       => $parentEmail !== '' ? $parentEmail : $this->buildFallbackEmail($parentCode),
                        'phone'       => $parentPhone,
                        'address'     => $parentAddress,
                        'city_code'   => $parentCityCode,
                        'city_name'   => $parentCityName,
                        'country'     => $parentCountry,
                        'latitude'    => $parentLatitude,
                        'longitude'   => $parentLongitude,
                    ]);

                    if ($parentAction === 'created') {
                        $summary['parent_created']++;
                    } else {
                        $summary['parent_updated']++;
                    }

                    $cust = $this->importCustomersForParentNpp($baseUrl, $parentUser, $parentCode);

                    $summary['customers_created']                += $cust['created'];
                    $summary['customers_updated']                += $cust['updated'];
                    $summary['customers_skipped']                += $cust['skipped'];
                    $summary['customer_duplicate_code_resolved'] += $cust['duplicate_code_resolved'];
                    $summary['customer_sync_parents']++;

                    foreach ($cust['errors'] as $err) {
                        $summary['customer_import_errors'][] = $err;
                    }

                    foreach ($cust['cases'] as $case) {
                        $summary['customer_cases'][] = $case;
                    }

                    // Gom danh mục từ tất cả showroom con để gán lại cho NPP cha.
                    // Không sync cha theo từng showroom riêng lẻ, vì dễ làm thiếu/xóa nhầm danh mục.
                    $parentCategoryCodesFromChildren = [];

                    $showrooms = $parentRow['showroom'] ?? [];
                    if (!is_array($showrooms)) {
                        $showrooms = [];
                    }

                    if (!empty($onlyShowroomCodes)) {
                        $onlyShowroomCodes = array_values(array_filter(array_map('strval', $onlyShowroomCodes)));

                        $showrooms = array_values(array_filter($showrooms, function ($row) use ($onlyShowroomCodes) {
                            return is_array($row)
                                && in_array((string) ($row['code'] ?? ''), $onlyShowroomCodes, true);
                        }));
                    }

                    foreach ($showrooms as $showroomIndex => $showroomRow) {
                        try {
                            if (!is_array($showroomRow)) {
                                throw new \RuntimeException("Showroom record #{$showroomIndex} của NPP {$parentCode} không hợp lệ");
                            }

                            $showroomCode = trim((string) ($showroomRow['code'] ?? ''));

                            if ($showroomCode === '') {
                                throw new \RuntimeException("Thiếu code showroom của NPP {$parentCode}");
                            }

                            $detailRow = $this->fetchShowroomDetailRow($baseUrl, $showroomCode);

                            if ($detailRow !== null) {
                                $summary['showroom_detail_synced']++;
                            } else {
                                $summary['showroom_detail_failed']++;
                            }

                            $mergedRow = $this->mergeShowroomRow($showroomRow, $detailRow);

                            $showroomName      = trim((string) ($mergedRow['name'] ?? ''));
                            $showroomEmail     = trim((string) ($mergedRow['email'] ?? ''));
                            $showroomPhone     = trim((string) ($mergedRow['phone'] ?? ''));
                            $showroomAddress   = trim((string) ($mergedRow['address'] ?? ''));
                            $showroomCityCode  = trim((string) ($mergedRow['city_code'] ?? ''));
                            $showroomCityName  = trim((string) ($mergedRow['city_name'] ?? ''));
                            $showroomCountry   = trim((string) ($mergedRow['country'] ?? $parentCountry));
                            $showroomLatitude  = $this->normalizeNullableNumeric($mergedRow['latitude'] ?? null);
                            $showroomLongitude = $this->normalizeNullableNumeric($mergedRow['longitude'] ?? null);

                            if ($showroomName === '') {
                                throw new \RuntimeException("Thiếu name showroom {$showroomCode}");
                            }

                            [$showroomUser, $showroomAction] = $this->upsertDealer([
                                'user_name'   => $showroomCode,
                                'code'        => $showroomCode,
                                'parent_id'   => $parentUser->id,
                                'parent_code' => $parentCode,
                                'source_code' => $parentCode,
                                'name'        => $showroomName,
                                'password'    => $this->generateDefaultDealerPassword($showroomCode),
                                'email'       => $showroomEmail !== '' ? $showroomEmail : $this->buildFallbackEmail($showroomCode),
                                'phone'       => $showroomPhone,
                                'address'     => $showroomAddress,
                                'city_code'   => $showroomCityCode,
                                'city_name'   => $showroomCityName,
                                'country'     => $showroomCountry,
                                'latitude'    => $showroomLatitude,
                                'longitude'   => $showroomLongitude,
                            ]);

                            $showroomCategoryCodes = $this->extractCategoryCodesFromRow($mergedRow);

                            // Showroom con vẫn được gán danh mục riêng như cũ.
                            $this->syncDealerCategoryCodes($showroomUser->id, $showroomCategoryCodes);

                            // NPP cha sẽ nhận hợp danh mục từ tất cả showroom con có danh mục.
                            $parentCategoryCodesFromChildren = array_merge(
                                $parentCategoryCodesFromChildren,
                                $showroomCategoryCodes
                            );

                            if ($showroomAction === 'created') {
                                $summary['showroom_created']++;
                            } else {
                                $summary['showroom_updated']++;
                            }
                        } catch (\Throwable $e) {
                            $summary['failed']++;
                            $summary['errors'][] = [
                                'parent_code'   => $parentCode,
                                'showroom_code' => $showroomRow['code'] ?? null,
                                'message'       => $e->getMessage(),
                            ];

                            Log::error('NppShowroomImporter: sync showroom failed', [
                                'parent_code' => $parentCode,
                                'showroom'    => $showroomRow,
                                'exception'   => $e->getMessage(),
                            ]);
                        }
                    }

                    $parentCategoryCodesFromChildren = array_values(array_unique(array_filter($parentCategoryCodesFromChildren)));

                    if (!empty($parentCategoryCodesFromChildren)) {
                        // Nếu đang import một phần showroom, không xóa danh mục cũ của NPP cha.
                        // Nếu đang import đầy đủ, cho phép đồng bộ lại để loại bỏ danh mục không còn tồn tại.
                        $deleteMissingParentCategories = empty($onlyShowroomCodes);

                        $this->syncDealerCategoryCodes(
                            $parentUser->id,
                            $parentCategoryCodesFromChildren,
                            $deleteMissingParentCategories
                        );
                    }
                } catch (\Throwable $e) {
                    $summary['failed']++;
                    $summary['errors'][] = [
                        'parent_code' => $parentRow['code'] ?? null,
                        'message'     => $e->getMessage(),
                    ];

                    Log::error('NppShowroomImporter: sync parent failed', [
                        'parent_row' => $parentRow,
                        'exception'  => $e->getMessage(),
                    ]);
                }
            }

            if ($dryRun) {
                DB::rollBack();

                return [
                    'success' => true,
                    'message' => 'Dry-run OK, đã rollback',
                    'data'    => $summary,
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Đồng bộ NPP / showroom / khách hàng hoàn tất',
                'data'    => $summary,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('NppShowroomImporter: fatal error', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi đồng bộ NPP / showroom / khách hàng',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{0: User, 1: 'created'|'updated'}
     */
    private function upsertDealer(array $data): array
    {
        $user = User::where('code', $data['code'])
            ->where('role', 'dealer')
            ->first();

        if (!$user) {
            $user = User::where('user_name', $data['user_name'])
                ->where('role', 'dealer')
                ->first();
        }

        $payload = [
            'user_name'   => $data['user_name'],
            'code'        => $data['code'],
            'parent_id'   => $data['parent_id'] ?? 0,
            'parent_code' => $data['parent_code'] ?? null,
            'source_code' => $data['source_code'] ?? null,
            'role'        => 'dealer',
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'address'     => $data['address'] ?? null,
            'city_code'   => $data['city_code'] ?? null,
            'city_name'   => $data['city_name'] ?? null,
            'country'     => $data['country'] ?? null,
            'latitude'    => $data['latitude'] ?? null,
            'longitude'   => $data['longitude'] ?? null,
            'status'      => 'active',
            'is_active'   => '1',
            'is_admin'    => '0',
            'F1UserID'    => '999999999',
            'TokenID'     => '999999999',
        ];

        if ($user) {
            if (empty($user->password)) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->refresh();

            return [$user, 'updated'];
        }

        $payload['password'] = Hash::make($data['password']);

        $user = User::create($payload);

        return [$user, 'created'];
    }

    private function extractCategoryCodesFromRow(array $row): array
    {
        $categoryCodes = [];

        if (!empty($row['item_category']) && is_array($row['item_category'])) {
            foreach ($row['item_category'] as $itemCategory) {
                $rawCode = trim((string) ($itemCategory['code'] ?? $itemCategory['group'] ?? ''));

                if ($rawCode !== '') {
                    $normalizedCode = $this->normalizeCategoryCode($rawCode);

                    if ($normalizedCode !== '') {
                        $categoryCodes[] = $normalizedCode;
                    }
                }
            }
        }

        if (empty($categoryCodes) && !empty($row['item']) && is_array($row['item'])) {
            foreach ($row['item'] as $item) {
                $rawCode = trim((string) ($item['group'] ?? ''));

                if ($rawCode !== '') {
                    $normalizedCode = $this->normalizeCategoryCode($rawCode);

                    if ($normalizedCode !== '') {
                        $categoryCodes[] = $normalizedCode;
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($categoryCodes)));
    }

    private function syncDealerCategories(int $userId, array $row): void
    {
        $this->syncDealerCategoryCodes(
            $userId,
            $this->extractCategoryCodesFromRow($row)
        );
    }

    /**
     * @param array<int, string> $categoryCodes
     */
    private function syncDealerCategoryCodes(int $userId, array $categoryCodes, bool $deleteMissing = true): void
    {
        $categoryCodes = array_values(array_unique(array_filter($categoryCodes)));

        if (empty($categoryCodes)) {
            if ($deleteMissing) {
                DB::table('npp_product_categories')
                    ->where('user_id', $userId)
                    ->delete();
            }

            return;
        }

        $categories = ProductCategory::whereIn('code', $categoryCodes)
            ->get()
            ->keyBy('code');

        $validCategoryIds = [];

        foreach ($categoryCodes as $code) {
            $category = $categories->get($code);

            if (!$category) {
                Log::warning('NppShowroomImporter: category not found', [
                    'user_id'  => $userId,
                    'raw_code' => $code,
                ]);

                continue;
            }

            $validCategoryIds[] = $category->id;
        }

        $validCategoryIds = array_values(array_unique($validCategoryIds));

        if (empty($validCategoryIds)) {
            if ($deleteMissing) {
                DB::table('npp_product_categories')
                    ->where('user_id', $userId)
                    ->delete();
            }

            return;
        }

        if ($deleteMissing) {
            DB::table('npp_product_categories')
                ->where('user_id', $userId)
                ->whereNotIn('category_id', $validCategoryIds)
                ->delete();
        }

        foreach ($validCategoryIds as $categoryId) {
            $exists = DB::table('npp_product_categories')
                ->where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->exists();

            if (!$exists) {
                DB::table('npp_product_categories')->insert([
                    'user_id'     => $userId,
                    'category_id' => $categoryId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function fetchSourceCodesPayload(string $baseUrl): ?array
    {
        $endpoint = rtrim($baseUrl, '/') . '/aprocess/sourcecodes/';
        $requestBody = [
            'client_id'       => config('services.casumina.client_id'),
            'client_password' => config('services.casumina.client_password'),
            'source_code'     => 'all',
        ];

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, $requestBody);

            if (!$response->successful()) {
                Log::error('NppShowroomImporter: sourcecodes API failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            return $this->normalizeApiListPayload($response->json());
        } catch (\Throwable $e) {
            Log::error('NppShowroomImporter: sourcecodes request exception', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchShowroomDetailRow(string $baseUrl, string $showroomCode): ?array
    {
        $endpoint = rtrim($baseUrl, '/') . '/aprocess/showroomg/';
        $requestBody = [
            'client_id'       => config('services.casumina.client_id'),
            'client_password' => config('services.casumina.client_password'),
            'showroom'        => $showroomCode,
        ];

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, $requestBody);

            if (!$response->successful()) {
                Log::warning('NppShowroomImporter: showroomg API failed', [
                    'showroom_code' => $showroomCode,
                    'status'        => $response->status(),
                    'body'          => $response->body(),
                ]);

                return null;
            }

            $rows = $this->normalizeApiListPayload($response->json());

            if (empty($rows)) {
                return null;
            }

            foreach ($rows as $row) {
                if (is_array($row) && trim((string) ($row['code'] ?? '')) === $showroomCode) {
                    return $row;
                }
            }

            return is_array($rows[0] ?? null) ? $rows[0] : null;
        } catch (\Throwable $e) {
            Log::warning('NppShowroomImporter: showroomg request exception', [
                'showroom_code' => $showroomCode,
                'exception'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param mixed $payload
     * @return list<array<string, mixed>>
     */
    private function normalizeApiListPayload($payload): array
    {
        if ($payload === null) {
            return [];
        }

        if (is_array($payload) && array_is_list($payload)) {
            return $payload;
        }

        if (is_array($payload)) {
            foreach (['data', 'items', 'records', 'sourcecodes', 'showrooms', 'customers'] as $key) {
                if (isset($payload[$key]) && is_array($payload[$key]) && array_is_list($payload[$key])) {
                    return $payload[$key];
                }
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $baseRow
     * @param array<string, mixed>|null $detailRow
     * @return array<string, mixed>
     */
    private function mergeShowroomRow(array $baseRow, ?array $detailRow): array
    {
        if ($detailRow === null) {
            return $baseRow;
        }

        $merged = $baseRow;

        foreach (
            [
                'code',
                'name',
                'address',
                'city_code',
                'city_name',
                'phone',
                'email',
                'country',
                'latitude',
                'longitude',
                'item_category',
                'item',
            ] as $field
        ) {
            if (array_key_exists($field, $detailRow) && $detailRow[$field] !== null && $detailRow[$field] !== '') {
                $merged[$field] = $detailRow[$field];
            }
        }

        return $merged;
    }

    /**
     * @param array<int, array<string, mixed>> $sourceRows
     * @param array<int, string> $onlyShowroomCodes
     */
    private function countNestedShowrooms(array $sourceRows, array $onlyShowroomCodes = []): int
    {
        $total = 0;

        foreach ($sourceRows as $row) {
            if (!is_array($row) || empty($row['showroom']) || !is_array($row['showroom'])) {
                continue;
            }

            $showrooms = $row['showroom'];

            if (!empty($onlyShowroomCodes)) {
                $onlyShowroomCodes = array_values(array_filter(array_map('strval', $onlyShowroomCodes)));

                $showrooms = array_values(array_filter($showrooms, function ($item) use ($onlyShowroomCodes) {
                    return is_array($item)
                        && in_array((string) ($item['code'] ?? ''), $onlyShowroomCodes, true);
                }));
            }

            $total += count($showrooms);
        }

        return $total;
    }

    private function normalizeCategoryCode(string $code): string
    {
        $code = trim($code);

        if ($code === '') {
            return '';
        }

        $code = preg_replace('/\s+/', '', $code);

        if (strpos($code, '-') !== false) {
            $code = str_replace('-', '_', $code);
        }

        return $code;
    }

    private function normalizeNullableNumeric($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function buildFallbackEmail(string $code): string
    {
        return strtolower($code) . '@no-email.local';
    }

    private function generateDefaultDealerPassword(string $code): string
    {
        return 'Casumina@' . $code;
    }

    /**
     * @return array{
     *   created: int,
     *   updated: int,
     *   skipped: int,
     *   duplicate_code_resolved: int,
     *   cases: list<array<string, mixed>>,
     *   errors: list<array{parent_code: string, message: string}>
     * }
     */
    private function importCustomersForParentNpp(string $baseUrl, User $parentDealer, string $parentCode): array
    {
        $out = [
            'created'                 => 0,
            'updated'                 => 0,
            'skipped'                 => 0,
            'duplicate_code_resolved' => 0,
            'cases'                   => [],
            'errors'                  => [],
        ];

        $rows = $this->fetchCustomersPayloadForSourceCode($baseUrl, $parentCode);

        if ($rows === null) {
            $out['errors'][] = [
                'parent_code' => $parentCode,
                'message'     => 'Không lấy được danh sách khách (API customerg)',
            ];

            return $out;
        }

        foreach ($rows as $idx => $row) {
            if (!is_array($row)) {
                $case = [
                    'parent_code' => $parentCode,
                    'index'       => $idx,
                    'action'      => 'skipped',
                    'reason'      => 'row_not_array',
                    'raw_row'     => $row,
                ];

                $out['skipped']++;
                $out['cases'][] = $case;

                Log::warning('NppShowroomImporter: customer skipped', $case);
                continue;
            }

            try {
                $result = $this->upsertCustomerFromCasuminaRow($parentDealer, $parentCode, $row, $idx);

                if ($result['action'] === 'created') {
                    $out['created']++;
                } elseif ($result['action'] === 'updated') {
                    $out['updated']++;
                } else {
                    $out['skipped']++;
                }

                if (!empty($result['duplicate_code_resolved'])) {
                    $out['duplicate_code_resolved']++;
                }

                $out['cases'][] = $result['case'];

                Log::info('NppShowroomImporter: customer import case', $result['case']);
            } catch (\Throwable $e) {
                $case = [
                    'parent_code' => $parentCode,
                    'index'       => $idx,
                    'action'      => 'error',
                    'reason'      => 'exception',
                    'message'     => $e->getMessage(),
                    'customer_no' => $row['customer_no'] ?? null,
                    'raw_row'     => $row,
                ];

                $out['errors'][] = [
                    'parent_code' => $parentCode,
                    'message'     => "#{$idx}: " . $e->getMessage(),
                ];

                $out['cases'][] = $case;

                Log::error('NppShowroomImporter: customer row failed', $case);
            }
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function fetchCustomersPayloadForSourceCode(string $baseUrl, string $sourceCode): ?array
    {
        $endpoint = rtrim($baseUrl, '/') . '/aprocess/customerg/';
        $requestBody = [
            'client_id'       => config('services.casumina.client_id'),
            'client_password' => config('services.casumina.client_password'),
            'source_code'     => $sourceCode,
        ];

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, $requestBody);

            if (!$response->successful()) {
                Log::error('NppShowroomImporter: customerg API failed', [
                    'source_code' => $sourceCode,
                    'status'      => $response->status(),
                    'body'        => $response->body(),
                ]);

                return null;
            }

            return $this->normalizeCustomersListPayload($response->json());
        } catch (\Throwable $e) {
            Log::error('NppShowroomImporter: customerg request exception', [
                'source_code' => $sourceCode,
                'exception'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param mixed $payload
     * @return list<array<string, mixed>>
     */
    private function normalizeCustomersListPayload($payload): array
    {
        return $this->normalizeApiListPayload($payload);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{
     *   action: 'created'|'updated'|'skipped',
     *   duplicate_code_resolved: bool,
     *   case: array<string, mixed>
     * }
     */
    private function upsertCustomerFromCasuminaRow(User $parentDealer, string $sourceCode, array $row, int $index = 0): array
    {
        $originalCustomerNo = trim((string) ($row['customer_no'] ?? $row['code'] ?? $row['customer_code'] ?? ''));

        if ($originalCustomerNo === '') {
            return [
                'action'                  => 'skipped',
                'duplicate_code_resolved' => false,
                'case' => [
                    'parent_code'          => $sourceCode,
                    'index'                => $index,
                    'action'               => 'skipped',
                    'reason'               => 'missing_customer_no',
                    'customer_no_original' => null,
                    'raw_row'              => $row,
                ],
            ];
        }

        $fullname = trim((string) ($row['fullname'] ?? $row['name'] ?? $row['customer_name'] ?? ''));
        if ($fullname === '') {
            $fullname = $originalCustomerNo;
        }

        $email = trim((string) ($row['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = null;
        }

        $rawPhone = trim((string) ($row['phone'] ?? $row['mobile'] ?? $row['tel'] ?? ''));
        $rawPhone = $rawPhone !== '' ? $rawPhone : null;

        $rawZalo = isset($row['zalo']) ? trim((string) $row['zalo']) : null;

        $address = trim((string) ($row['address'] ?? ''));
        if ($address === '') {
            $address = '-';
        }

        $cityCode = trim((string) ($row['city_code'] ?? ''));
        $cityName = trim((string) ($row['city_name'] ?? $row['city'] ?? ''));
        $country  = trim((string) ($row['country'] ?? ''));

        $duplicateCodeResolved = false;
        $resolutionNotes = [];

        $existingByCode = User::where('code', $originalCustomerNo)->first();

        $existingCustomer = null;
        $finalCustomerCode = $originalCustomerNo;

        if ($existingByCode) {
            if ($existingByCode->role === 'customer' && $existingByCode->type === 'customer_info') {
                $existingCustomer = $existingByCode;
            } else {
                $duplicateCodeResolved = true;
                $finalCustomerCode = $this->makeUniquePrefixedCustomerCode($sourceCode, $originalCustomerNo);
                $resolutionNotes[] = "code_conflict_resolved: {$originalCustomerNo} -> {$finalCustomerCode}";
            }
        }

        if ($existingCustomer) {
            $finalPhone = $this->resolveUniqueCustomerPhone(
                $rawPhone,
                $sourceCode,
                $originalCustomerNo,
                (int) $existingCustomer->id
            );

            if ($rawPhone !== null && $finalPhone !== $rawPhone) {
                $resolutionNotes[] = "phone_conflict_resolved: {$rawPhone} -> {$finalPhone}";
            }

            $finalEmail = $this->resolveUniqueCustomerEmail(
                $email,
                $sourceCode,
                $originalCustomerNo,
                (int) $existingCustomer->id
            );

            if ($email !== null && $finalEmail !== $email) {
                $resolutionNotes[] = "email_conflict_resolved: {$email} -> {$finalEmail}";
            }

            $zaloForSave = $rawZalo;
            if (($zaloForSave === null || $zaloForSave === '') && $rawPhone !== null && $finalPhone !== $rawPhone) {
                $zaloForSave = $rawPhone;
            }

            $updateData = [
                'parent_id'   => $parentDealer->id,
                'parent_code' => $sourceCode,
                'name'        => $fullname,
                'address'     => $address,
                'city_code'   => $cityCode,
                'city_name'   => $cityName,
                'country'     => $country,
                'email'       => $finalEmail,
                'phone'       => $finalPhone,
            ];

            if ($zaloForSave !== null && $zaloForSave !== '') {
                $updateData['zalo'] = $zaloForSave;
            }

            foreach (['facebook', 'vehicle', 'license_plate'] as $opt) {
                if (array_key_exists($opt, $row) && $row[$opt] !== null && trim((string) $row[$opt]) !== '') {
                    $updateData[$opt] = trim((string) $row[$opt]);
                }
            }

            $existingCustomer->update($updateData);

            return [
                'action'                  => 'updated',
                'duplicate_code_resolved' => $duplicateCodeResolved,
                'case' => [
                    'parent_code'          => $sourceCode,
                    'index'                => $index,
                    'action'               => 'updated',
                    'reason'               => 'matched_existing_customer_by_code',
                    'user_id'              => $existingCustomer->id,
                    'customer_no_original' => $originalCustomerNo,
                    'customer_no_final'    => $existingCustomer->code,
                    'email_final'          => $finalEmail,
                    'phone_final'          => $finalPhone,
                    'notes'                => $resolutionNotes,
                ],
            ];
        }

        $username = $this->makeUniqueCustomerUsername($sourceCode, $originalCustomerNo);

        $finalPhone = $this->resolveUniqueCustomerPhone(
            $rawPhone,
            $sourceCode,
            $originalCustomerNo
        );

        if ($rawPhone !== null && $finalPhone !== $rawPhone) {
            $resolutionNotes[] = "phone_conflict_resolved: {$rawPhone} -> {$finalPhone}";
        }

        $finalEmail = $this->resolveUniqueCustomerEmail(
            $email,
            $sourceCode,
            $originalCustomerNo
        );

        if ($email !== null && $finalEmail !== $email) {
            $resolutionNotes[] = "email_conflict_resolved: {$email} -> {$finalEmail}";
        }

        $zaloForSave = $rawZalo;
        if (($zaloForSave === null || $zaloForSave === '') && $rawPhone !== null && $finalPhone !== $rawPhone) {
            $zaloForSave = $rawPhone;
        }

        $created = User::create([
            'code'          => $finalCustomerCode,
            'parent_code'   => $sourceCode,
            'user_name'     => $username,
            'parent_id'     => $parentDealer->id,
            'type'          => 'customer_info',
            'role'          => 'customer',
            'name'          => $fullname,
            'email'         => $finalEmail,
            'phone'         => $finalPhone,
            'address'       => $address,
            'city_code'     => $cityCode,
            'city_name'     => $cityName,
            'country'       => $country,
            'zalo'          => $zaloForSave,
            'facebook'      => isset($row['facebook']) ? trim((string) $row['facebook']) : null,
            'vehicle'       => isset($row['vehicle']) ? trim((string) $row['vehicle']) : null,
            'license_plate' => isset($row['license_plate']) ? trim((string) $row['license_plate']) : null,
            'status'        => 'active',
            'is_active'     => '1',
            'is_admin'      => '0',
        ]);

        return [
            'action'                  => 'created',
            'duplicate_code_resolved' => $duplicateCodeResolved,
            'case' => [
                'parent_code'          => $sourceCode,
                'index'                => $index,
                'action'               => 'created',
                'reason'               => $duplicateCodeResolved ? 'created_with_prefixed_code' : 'created',
                'user_id'              => $created->id,
                'customer_no_original' => $originalCustomerNo,
                'customer_no_final'    => $finalCustomerCode,
                'email_final'          => $finalEmail,
                'phone_final'          => $finalPhone,
                'notes'                => $resolutionNotes,
            ],
        ];
    }

    private function resolveUniqueCustomerPhone(?string $phone, string $sourceCode, string $customerNo, ?int $ignoreUserId = null): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $phone = trim($phone);

        $query = User::where('phone', $phone);
        if ($ignoreUserId !== null) {
            $query->where('id', '!=', $ignoreUserId);
        }

        if (!$query->exists()) {
            return $phone;
        }

        $base = 'dup_' . $this->sanitizeToken($sourceCode) . '_' . $this->sanitizeToken($customerNo) . '_' . $this->sanitizeToken($phone);
        $base = substr($base, 0, 190);

        $candidate = $base;
        $i = 1;

        while (true) {
            $check = User::where('phone', $candidate);
            if ($ignoreUserId !== null) {
                $check->where('id', '!=', $ignoreUserId);
            }

            if (!$check->exists()) {
                return $candidate;
            }

            $suffix = '_' . $i;
            $candidate = substr($base, 0, 190 - strlen($suffix)) . $suffix;
            $i++;
        }
    }

    private function resolveUniqueCustomerEmail(?string $email, string $sourceCode, string $customerNo, ?int $ignoreUserId = null): ?string
    {
        if ($email === null || trim($email) === '') {
            return null;
        }

        $email = trim($email);

        $query = User::where('email', $email);
        if ($ignoreUserId !== null) {
            $query->where('id', '!=', $ignoreUserId);
        }

        if (!$query->exists()) {
            return $email;
        }

        $safeSource = strtolower($this->sanitizeToken($sourceCode));
        $safeCustomer = strtolower($this->sanitizeToken($customerNo));

        $base = 'dup_' . $safeSource . '_' . $safeCustomer . '@npp-import.local';
        $candidate = $base;
        $i = 1;

        while (true) {
            $check = User::where('email', $candidate);
            if ($ignoreUserId !== null) {
                $check->where('id', '!=', $ignoreUserId);
            }

            if (!$check->exists()) {
                return $candidate;
            }

            $candidate = 'dup_' . $safeSource . '_' . $safeCustomer . '_' . $i . '@npp-import.local';
            $i++;
        }
    }
    private function makeUniquePrefixedCustomerCode(string $sourceCode, string $customerNo): string
    {
        $base = 'CUST_' . $this->sanitizeToken($sourceCode) . '_' . $this->sanitizeToken($customerNo);
        $base = substr($base, 0, 100);

        $candidate = $base;
        $i = 1;

        while (User::where('code', $candidate)->exists()) {
            $suffix = '_' . $i;
            $candidate = substr($base, 0, 100 - strlen($suffix)) . $suffix;
            $i++;
        }

        return $candidate;
    }

    private function makeUniqueCustomerUsername(string $sourceCode, string $customerNo): string
    {
        $base = 'customer_' . $this->sanitizeToken($sourceCode) . '_' . $this->sanitizeToken($customerNo);
        $base = substr($base, 0, 120);

        $candidate = $base;
        $i = 1;

        while (User::where('user_name', $candidate)->exists()) {
            $suffix = '_' . $i;
            $candidate = substr($base, 0, 120 - strlen($suffix)) . $suffix;
            $i++;
        }

        return $candidate;
    }

    private function sanitizeToken(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^A-Za-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value !== '' ? $value : 'X';
    }

    private function buildFallbackCustomerEmail(string $customerNo): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9]+/', '.', $customerNo);

        return strtolower((string) $safe) . '@npp-import.local';
    }
}
