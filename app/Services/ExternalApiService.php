<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service gọi API bên ngoài (maincsm.com) để lấy danh mục và sản phẩm.
 */
class ExternalApiService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientPassword;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl        = env('CASUMINA_API_URL', config('services.casumina.url'));
        $this->clientId       = config('services.casumina.client_id');
        $this->clientPassword = config('services.casumina.client_password');
        $this->timeout        = (int) config('services.casumina.timeout', 60);
    }

    /**
     * Lấy toàn bộ danh mục sản phẩm từ API.
     *
     * @return array  [['id' => '01', 'name' => 'Săm lốp xe tải', ...], ...]
     * @throws \RuntimeException
     */
    public function getCategories(): array
    {
        return $this->post('/aprocess/itemcategoryg/', []);
    }

    /**
     * Lấy danh sách sản phẩm theo mã danh mục.
     *
     * @param string $categoryCode  e.g. "01"
     * @return array
     * @throws \RuntimeException
     */
    public function getProductsByCategory(string $categoryCode): array
    {
        return $this->post('/aprocess/itemg/', [
            'item_category_code' => $categoryCode,
        ]);
    }

    /**
     * Lấy thông tin 1 sản phẩm theo mã item_no (SKU).
     * API endpoint /aprocess/itemg/ hỗ trợ filter theo item_no.
     *
     * @param string $itemNo  Mã sản phẩm, e.g. "21060071"
     * @return array|null     Dữ liệu sản phẩm, hoặc null nếu không tìm thấy
     * @throws \RuntimeException
     */
    public function getProductByItemNo(string $itemNo): ?array
    {
        $results = $this->post('/aprocess/itemg/', [
            'item_no' => $itemNo,
        ]);

        // API trả về array; lấy phần tử đầu tiên khớp item_no
        foreach ($results as $item) {
            if (isset($item['item_no']) && (string) $item['item_no'] === $itemNo) {
                return $item;
            }
        }

        // Nếu API trả về đúng 1 phần tử không có item_no check → trả về phần tử đó
        if (count($results) === 1) {
            return $results[0];
        }

        return null;
    }

    /**
     * Lấy giá sản phẩm theo item_no.
     * POST https://mit.maincsm.com:2036/aprocess/priceg/ body: {"item_no": "21060071"}
     *
     * @param string      $itemNo      Mã sản phẩm (item_no)
     * @param string|null $httpMethod  'post' hoặc 'get' (do caller định nghĩa, mặc định 'post')
     * @return float|null  Giá (số) hoặc null nếu không lấy được
     */
    public function getPriceByItemNo(string $itemNo, ?string $httpMethod = null): ?float
    {
        $itemNo = trim($itemNo);
        if ($itemNo === '') {
            return null;
        }
        $method = $httpMethod !== null ? strtolower(trim($httpMethod)) : 'post';
        if ($method !== 'get') {
            $method = 'post';
        }

        try {
            $data = $this->fetchPriceg($itemNo, $method);
            if (is_array($data) && isset($data[0])) {
                $first = $data[0];
                $price = $first['price'] ?? $first['Price'] ?? null;
                if ($price !== null && $price !== '') {
                    $value = is_numeric($price) ? (float) $price : (float) preg_replace('/[^0-9.]/', '', (string) $price);
                    return $value > 0 ? $value : null;
                }
                Log::channel('import_external')->debug('API priceg trả về giá rỗng', [
                    'item_no' => $itemNo,
                    'response_item_no' => $first['item_no'] ?? null,
                ]);
            }
            return null;
        } catch (\Throwable $e) {
            Log::warning('ExternalApiService: getPriceByItemNo failed', ['item_no' => $itemNo, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Gọi API priceg (lấy giá theo item_no).
     * Method do caller truyền vào ('post' hoặc 'get').
     *
     * @param string $itemNo
     * @param string $method  'post' | 'get'
     * @return array
     */
    private function fetchPriceg(string $itemNo, string $method = 'post'): array
    {
        $endpoint = '/aprocess/priceg/';
        $params = ['item_no' => $itemNo];

        if ($method === 'get') {
            return $this->get($endpoint, $params);
        }
        return $this->post($endpoint, $params);
    }

    /**
     * GET request (query string) dùng cho priceg khi API chỉ hỗ trợ GET.
     */
    private function get(string $endpoint, array $query = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $query = array_merge([
            'client_id'       => $this->clientId,
            'client_password' => $this->clientPassword,
        ], $query);

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->get($url, $query);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "API {$url} trả về HTTP " . $response->getStatusCode() . ': ' . $response->body()
                );
            }
            $data = $response->json();
            if ($data === null) {
                throw new \RuntimeException("API {$url} trả về JSON không hợp lệ: " . $response->body());
            }
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }
            return is_array($data) ? $data : [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("ExternalApiService: Không thể kết nối {$url}", ['error' => $e->getMessage()]);
            throw new \RuntimeException("Không thể kết nối API: " . $e->getMessage());
        }
    }

    /**
     * Gọi POST request đến API, trả về mảng dữ liệu.
     */
    private function post(string $endpoint, array $extra = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $body = array_merge([
            'client_id'       => $this->clientId,
            'client_password' => $this->clientPassword,
        ], $extra);

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()   // bỏ SSL verify nếu cert self-signed
                ->post($url, $body);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "API {$url} trả về HTTP " . $response->status() . ': ' . $response->body()
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw new \RuntimeException("API {$url} trả về JSON không hợp lệ: " . $response->body());
            }

            // API có thể trả về array thẳng hoặc bọc trong key 'data'
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }

            return is_array($data) ? $data : [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("ExternalApiService: Không thể kết nối {$url}", ['error' => $e->getMessage()]);
            throw new \RuntimeException("Không thể kết nối API: " . $e->getMessage());
        }
    }
}
