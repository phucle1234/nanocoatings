<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    private const ALLOWED_CATEGORY_CODES = ['01', '02', '03', '04', 'quocte'];
    private const PER_PAGE = 15;

    private const DEALER_COLUMNS = [
        'u.id',
        'u.parent_id',
        'u.code',
        'u.parent_code',
        'u.name',
        'u.email',
        'u.phone',
        'u.address',
        'u.city_code',
        'u.city_name',
        'u.country',
        'u.latitude',
        'u.longitude',
        'u.link_map',
        'u.type',
    ];

    public function branch(Request $request)
    {
        return $this->renderBranchPage($request, 'langding.branch');
    }

    public function distributionSystem(Request $request)
    {
        try {
            $countries = $this->getCountries();

            // Trang distribution dùng Mapbox world view.
            // Không load chung danh sách dealer ban đầu để tránh lẫn cha/con với trang branch.
            return view('langding.distribution-system', [
                'dealers' => [],
                'pagination' => $this->makePagination(1, 0, self::PER_PAGE),
                'countries' => $countries,
            ]);
        } catch (\Throwable $e) {
            Log::error('Distribution system page error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()
                ->route('home')
                ->with('toast_error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function renderBranchPage(Request $request, string $viewName)
    {
        try {
            $page = max((int) $request->get('page', 1), 1);
            $countries = $this->getCountries();
            $allowedCategoryIds = $this->getAllowedCategoryIdsByCode();

            if (empty($allowedCategoryIds)) {
                return view($viewName, [
                    'dealers' => [],
                    'pagination' => $this->makePagination(1, 0, self::PER_PAGE),
                    'countries' => $countries,
                ]);
            }

            // Trang branch chỉ hiển thị dealer/showroom con.
            $query = $this->onlyChildDealers(
                $this->dealerQuery($allowedCategoryIds)
            )->orderBy('u.name');

            $total = (clone $query)->count();
            $users = (clone $query)
                ->forPage($page, self::PER_PAGE)
                ->get();

            $dealers = $this->mapDealers($users)->toArray();
            $pagination = $this->makePagination($page, $total, self::PER_PAGE);

            return view($viewName, compact('dealers', 'pagination', 'countries'));
        } catch (\Throwable $e) {
            Log::error('Branch page error: ' . $e->getMessage(), [
                'exception' => $e,
                'view' => $viewName,
            ]);

            return redirect()
                ->route('home')
                ->with('toast_error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function getProvinces($countryId)
    {
        if (empty($countryId) || !is_numeric($countryId)) {
            return response()->json([
                'success' => false,
                'provinces' => [],
                'message' => 'Invalid country ID',
            ]);
        }

        $locale = app()->getLocale();
        $nameField = $locale === 'vi' ? 'name_vi' : 'name_en';

        $provinces = DB::table('npp_provinces')
            ->where('country_id', (int) $countryId)
            ->select('id', 'country_id', 'name_vi', 'name_en', 'code', 'type')
            ->orderBy($nameField)
            ->get();

        return response()->json([
            'success' => true,
            'provinces' => $provinces,
        ]);
    }

    public function getCategories($provinceCode)
    {
        $locale = app()->getLocale();

        $categories = DB::table('product_categories as pc')
            ->join('product_category_translations as pct', function ($join) use ($locale) {
                $join->on('pc.id', '=', 'pct.category_id')
                    ->where('pct.language', $locale);
            })
            ->whereIn('pc.code', self::ALLOWED_CATEGORY_CODES)
            ->select('pc.id', 'pc.code', 'pct.name', 'pct.slug')
            ->orderByRaw($this->buildCodeOrderSql(self::ALLOWED_CATEGORY_CODES))
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    public function searchDealers(Request $request)
    {
        $validated = $request->validate([
            'province_code' => 'required|string',
            'category_code' => 'nullable|string',
        ]);

        $allowedCategoryMap = $this->getAllowedCategoryMapByCode();
        $allowedCategoryIds = array_values($allowedCategoryMap);

        if (empty($allowedCategoryIds)) {
            return $this->dealerJsonResponse(collect());
        }

        $categoryId = $this->resolveCategoryIdByCode($validated['category_code'] ?? null, $allowedCategoryMap);
        $provinceCode = trim((string) $validated['province_code']);

        $users = $this->onlyChildDealers(
            $this->dealerQuery($allowedCategoryIds, $categoryId)
        )
            ->where('u.city_code', $provinceCode)
            ->orderBy('u.name')
            ->get();

        return $this->dealerJsonResponse($users);
    }

    public function searchAllDealers()
    {
        $allowedCategoryIds = $this->getAllowedCategoryIdsByCode();

        if (empty($allowedCategoryIds)) {
            return response()->json(['success' => true, 'dealers' => []]);
        }

        $users = $this->onlyChildDealers(
            $this->dealerQuery($allowedCategoryIds)
        )
            ->orderBy('u.name')
            ->get();

        return $this->dealerJsonResponse($users);
    }

    /**
     * Distribution page: only parent distributors.
     * Parent distributor = users.parent_code is NULL or empty string.
     */
    public function searchDistributors(Request $request)
    {
        $validated = $request->validate([
            'province_code' => 'required|string',
            'category_code' => 'nullable|string',
        ]);

        $allowedCategoryMap = $this->getAllowedCategoryMapByCode();
        $allowedCategoryIds = array_values($allowedCategoryMap);

        if (empty($allowedCategoryIds)) {
            return $this->dealerJsonResponse(collect());
        }

        $categoryId = $this->resolveCategoryIdByCode($validated['category_code'] ?? null, $allowedCategoryMap);
        $provinceCode = trim((string) $validated['province_code']);

        $users = $this->onlyParentDistributors(
            $this->dealerQuery($allowedCategoryIds, $categoryId)
        )
            ->where('u.city_code', $provinceCode)
            ->orderBy('u.name')
            ->get();

        return $this->dealerJsonResponse($users);
    }

    /**
     * Distribution page: nearest parent distributors.
     */
    public function searchNearestDistributors(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'category_code' => 'nullable|string',
        ]);

        $allowedCategoryMap = $this->getAllowedCategoryMapByCode();
        $allowedCategoryIds = array_values($allowedCategoryMap);

        if (empty($allowedCategoryIds)) {
            return $this->dealerJsonResponse(collect(), true);
        }

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $categoryId = $this->resolveCategoryIdByCode($validated['category_code'] ?? null, $allowedCategoryMap);

        $users = $this->onlyParentDistributors(
            $this->dealerQuery($allowedCategoryIds, $categoryId)
        )
            // Không lọc cứng latitude/longitude. Bản ghi thiếu tọa độ vẫn có thể hiện trong danh sách.
            // Nếu thiếu tọa độ thì distance được đẩy xuống cuối.
            ->selectRaw($this->distanceSelectSql(), [$lat, $lng, $lat])
            ->orderBy('distance')
            ->orderBy('u.name')
            ->limit(10)
            ->get();

        return $this->dealerJsonResponse($users, true);
    }

    /**
     * Distribution page: child showrooms of selected distributor.
     * Child showroom = users.parent_code equals parent distributor code.
     */
    public function getDistributorShowrooms(Request $request, string $code)
    {
        $validated = $request->validate([
            'province_code' => 'nullable|string',
            'category_code' => 'nullable|string',
        ]);

        $distributorCode = trim($code);

        if ($distributorCode === '') {
            return $this->dealerJsonResponse(collect());
        }

        $allowedCategoryMap = $this->getAllowedCategoryMapByCode();
        $allowedCategoryIds = array_values($allowedCategoryMap);

        if (empty($allowedCategoryIds)) {
            return $this->dealerJsonResponse(collect());
        }

        $categoryId = $this->resolveCategoryIdByCode($validated['category_code'] ?? null, $allowedCategoryMap);

        $query = $this->dealerQuery($allowedCategoryIds, $categoryId)
            ->where('u.parent_code', $distributorCode);

        if (!empty($validated['province_code'])) {
            $query->where('u.city_code', trim((string) $validated['province_code']));
        }

        $users = $query->orderBy('u.name')->get();

        return $this->dealerJsonResponse($users);
    }

    private function resolveCategoryIdByCode($rawCategoryCode, array $allowedCategoryMap): ?int
    {
        if ($rawCategoryCode === null || $rawCategoryCode === '') {
            return null;
        }

        $categoryCode = trim((string) $rawCategoryCode);

        if (!array_key_exists($categoryCode, $allowedCategoryMap)) {
            throw ValidationException::withMessages([
                'category_code' => 'Danh mục sản phẩm không hợp lệ',
            ]);
        }

        return (int) $allowedCategoryMap[$categoryCode];
    }

    public function searchNearestDealers(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'category_code' => 'nullable|string',
        ]);

        $allowedCategoryMap = $this->getAllowedCategoryMapByCode();
        $allowedCategoryIds = array_values($allowedCategoryMap);

        if (empty($allowedCategoryIds)) {
            return $this->dealerJsonResponse(collect(), true);
        }

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $categoryId = $this->resolveCategoryIdByCode($validated['category_code'] ?? null, $allowedCategoryMap);

        $users = $this->onlyChildDealers(
            $this->dealerQuery($allowedCategoryIds, $categoryId)
        )
            // Không lọc cứng latitude/longitude. Bản ghi thiếu tọa độ vẫn có thể hiện trong danh sách.
            // Nếu thiếu tọa độ thì distance được đẩy xuống cuối.
            ->selectRaw($this->distanceSelectSql(), [$lat, $lng, $lat])
            ->orderBy('distance')
            ->orderBy('u.name')
            ->limit(10)
            ->get();

        return $this->dealerJsonResponse($users, true);
    }

    private function getCountries()
    {
        $locale = app()->getLocale();
        $nameField = $locale === 'vi' ? 'name_vi' : 'name_en';

        $columns = ['id', 'name_vi', 'name_en', 'code', 'phone_code', 'region'];

        // Trang distribution dùng Mapbox world view như daily.blade.
        // Nếu bảng npp_countries có tọa độ/zoom thì select thêm để JS render marker quốc gia.
        foreach (['latitude', 'longitude', 'default_zoom'] as $optionalColumn) {
            if (Schema::hasColumn('npp_countries', $optionalColumn)) {
                $columns[] = $optionalColumn;
            }
        }

        return DB::table('npp_countries')
            ->select($columns)
            ->orderByRaw("CASE WHEN code = 'VN' THEN 0 ELSE 1 END")
            ->orderBy($nameField)
            ->get();
    }

    private function dealerQuery(array $allowedCategoryIds, ?int $categoryId = null)
    {
        $categoryIds = $categoryId !== null ? [$categoryId] : $allowedCategoryIds;

        return DB::table('users as u')
            ->where('u.status', 'active')
            ->whereExists(function ($query) use ($categoryIds) {
                $query->select(DB::raw(1))
                    ->from('npp_product_categories as npc')
                    ->whereColumn('npc.user_id', 'u.id')
                    ->whereIn('npc.category_id', $categoryIds);
            })
            ->select(self::DEALER_COLUMNS);
    }

    private function onlyChildDealers($query)
    {
        return $query
            ->whereNotNull('u.parent_code')
            ->where('u.parent_code', '<>', '');
    }

    private function onlyParentDistributors($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('u.parent_code')
                    ->orWhere('u.parent_code', '');
            })
            ->whereNotNull('u.code')
            ->where('u.code', '<>', '');
    }

    private function distanceSelectSql(): string
    {
        return "
            CASE
                WHEN u.latitude IS NULL
                    OR u.longitude IS NULL
                    OR TRIM(CAST(u.latitude AS CHAR)) = ''
                    OR TRIM(CAST(u.longitude AS CHAR)) = ''
                THEN 999999
                ELSE (
                    6371 * ACOS(
                        LEAST(
                            1,
                            GREATEST(
                                -1,
                                COS(RADIANS(?))
                                * COS(RADIANS(CAST(u.latitude AS DECIMAL(12,8))))
                                * COS(RADIANS(CAST(u.longitude AS DECIMAL(12,8))) - RADIANS(?))
                                + SIN(RADIANS(?))
                                * SIN(RADIANS(CAST(u.latitude AS DECIMAL(12,8))))
                            )
                        )
                    )
                )
            END AS distance
        ";
    }

    private function getAllowedCategoryMapByCode(): array
    {
        return DB::table('product_categories')
            ->whereIn('code', self::ALLOWED_CATEGORY_CODES)
            ->pluck('id', 'code')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    private function dealerJsonResponse($users, bool $includeDistance = false)
    {
        $dealers = $this->mapDealers($users, $includeDistance);

        return response()->json([
            'success' => true,
            'dealers' => $dealers->values(),
            'count' => $dealers->count(),
        ]);
    }

    private function mapDealers($users, bool $includeDistance = false)
    {
        return collect($users)->map(function ($user) use ($includeDistance) {
            $dealer = [
                'id' => $user->id,
                'parent_id' => $user->parent_id ?? null,
                'code' => $user->code ?? null,
                'parent_code' => $user->parent_code ?? null,
                'type' => $user->type ?? null,
                'title' => $user->name ?? 'N/A',
                'content' => $this->buildContentHtml(
                    $user->address ?? '',
                    $user->phone ?? '',
                    $user->email ?? ''
                ),
                'content_map' => $this->buildContentHtmlMap(
                    $user->address ?? '',
                    $user->phone ?? '',
                    $user->email ?? ''
                ),
                'excerpt' => $this->makeCoordinates($user->latitude ?? null, $user->longitude ?? null),
                'link_map' => $this->normalizeExternalUrl($user->link_map ?? null),
                'directions_url' => $this->makeDirectionsUrl(
                    $user->link_map ?? null,
                    $user->latitude ?? null,
                    $user->longitude ?? null,
                    $user->address ?? null
                ),
                'phonebranch' => $user->phone ?? null,
                'address' => $user->address ?? null,
                'email' => $user->email ?? null,
                'city_code' => $user->city_code ?? null,
                'city_name' => $user->city_name ?? null,
                'country' => $user->country ?? null,
                'latitude' => $user->latitude ?? null,
                'longitude' => $user->longitude ?? null,
            ];

            if ($includeDistance && isset($user->distance)) {
                $dealer['distance_km'] = round((float) $user->distance, 3);
            }

            return $dealer;
        });
    }

    private function normalizeExternalUrl($url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            return '';
        }

        return $url;
    }

    private function makeDirectionsUrl($linkMap, $latitude, $longitude, ?string $address = null): string
    {
        $cleanLink = $this->normalizeExternalUrl($linkMap);

        if ($cleanLink !== '') {
            return $cleanLink;
        }

        $coordinates = $this->makeCoordinates($latitude, $longitude);

        if ($coordinates !== '') {
            return 'https://www.google.com/maps?q=' . rawurlencode($coordinates);
        }

        $address = trim((string) $address);

        if ($address !== '') {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
        }

        return '';
    }

    private function makeCoordinates($latitude, $longitude): string
    {
        $latitude = trim((string) $latitude);
        $longitude = trim((string) $longitude);

        if ($latitude === '' || $longitude === '') {
            return '';
        }

        return "{$latitude},{$longitude}";
    }

    private function resolveCategoryId($rawCategoryId, array $allowedCategoryIds): ?int
    {
        if ($rawCategoryId === null || $rawCategoryId === '') {
            return null;
        }

        $categoryId = (int) $rawCategoryId;

        if ($categoryId <= 0 || !in_array($categoryId, $allowedCategoryIds, true)) {
            throw ValidationException::withMessages([
                'category_id' => 'Danh mục sản phẩm không hợp lệ',
            ]);
        }

        return $categoryId;
    }

    private function makePagination(int $page, int $total, int $perPage): array
    {
        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    private function getAllowedCategoryIdsByCode(): array
    {
        return DB::table('product_categories')
            ->whereIn('code', self::ALLOWED_CATEGORY_CODES)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    private function buildCodeOrderSql(array $codes): string
    {
        $parts = [];

        foreach ($codes as $index => $code) {
            $safeCode = str_replace("'", "''", $code);
            $parts[] = "WHEN pc.code = '{$safeCode}' THEN {$index}";
        }

        return 'CASE ' . implode(' ', $parts) . ' ELSE 999 END';
    }

    private function buildContentHtml(?string $address, ?string $phone, ?string $email): string
    {
        $html = '<ul class="list-unstyled mb-0">';

        if (!empty($address)) {
            $html .= '<li class="d-flex align-items-center gap-2">';
            $html .= '<img src="' . asset('langding/imgs/icon-location.svg') . '" alt="Icon" width="13">';
            $html .= '<a href="#" class="text-muted fs-16 opacity-75">' . e($address) . '</a></li>';
        }

        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            $html .= '<li class="d-flex align-items-center gap-2 mt-2">';
            $html .= '<img src="' . asset('langding/imgs/telephone-call.svg') . '" alt="Icon" width="16">';
            $html .= '<a href="tel:' . e($cleanPhone) . '" class="text-muted fs-16 opacity-75">' . e($phone) . '</a></li>';
        }

        if (!empty($email)) {
            $html .= '<li class="d-flex align-items-center gap-2 mt-2">';
            $html .= '<img src="' . asset('langding/imgs/icon-mail.svg') . '" alt="Icon" width="16">';
            $html .= '<a href="mailto:' . e($email) . '" class="text-muted fs-16 opacity-75">' . e($email) . '</a></li>';
        }

        return $html . '</ul>';
    }

    private function buildContentHtmlMap(?string $address, ?string $phone, ?string $email): string
    {
        $html = '<ul class="list-unstyled mb-0">';

        if (!empty($address)) {
            $html .= '<li class="d-flex align-items-center gap-2">';
            $html .= '<img src="' . asset('langding/imgs/icon-location.svg') . '" alt="Icon" width="12">';
            $html .= '<a href="#" class="text-muted fs-12 opacity-75">' . e($address) . '</a></li>';
        }

        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            $html .= '<li class="d-flex align-items-center gap-2 mt-2">';
            $html .= '<img src="' . asset('langding/imgs/telephone-call.svg') . '" alt="Icon" width="14">';
            $html .= '<a href="tel:' . e($cleanPhone) . '" class="text-muted fs-12 opacity-75">' . e($phone) . '</a></li>';
        }

        if (!empty($email)) {
            $html .= '<li class="d-flex align-items-center gap-2 mt-2">';
            $html .= '<img src="' . asset('langding/imgs/icon-mail.svg') . '" alt="Icon" width="14">';
            $html .= '<a href="mailto:' . e($email) . '" class="text-muted fs-12 opacity-75">' . e($email) . '</a></li>';
        }

        return $html . '</ul>';
    }
}
