<?php

namespace App\Services\CasuminaApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use App\Services\ApiOutboundLogger;

class CasuminaApiClient
{
    protected string $apiUrl;
    protected string $clientId;
    protected string $clientPassword;
    protected ApiOutboundLogger $logger;

    public function __construct(ApiOutboundLogger $logger)
    {
        $this->apiUrl = env('CASUMINA_API_URL', config('services.casumina.url'));
        $this->clientId = config('services.casumina.client_id');
        $this->clientPassword = config('services.casumina.client_password');
        $this->logger = $logger;
    }

    /**
     * Tạo HTTP Client với config chung
     */
    protected function client(): PendingRequest
    {
        return Http::withOptions([
            'verify' => false,
        ])
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',  // ← QUAN TRỌNG
            ])
            ->timeout(30)
            ->retry(3, 100);
    }

    /**
     * Thêm credentials vào data
     */
    protected function withCredentials(array $data = []): array
    {
        return array_merge([
            'client_id' => $this->clientId,
            'client_password' => $this->clientPassword,
        ], $data);
    }

    /**
     * GET request
     */
    protected function get(string $endpoint = '', array $params = []): mixed
    {
        try {
            $url = $this->apiUrl . $endpoint;
            $queryParams = $this->withCredentials($params);

            $response = $this->client()->get($url, $queryParams);

            return $this->handleResponse($response, 'GET', $endpoint, $params);
        } catch (\Exception $e) {
            return $this->handleException($e, 'GET', $endpoint, $params);
        }
    }

    /**
     * POST request
     */
    protected function post(string $endpoint = '', array $data = []): mixed
    {
        $url = $this->apiUrl . $endpoint;
        $payload = $this->withCredentials($data);
        // Mốc thời gian thực tế bắt đầu request
        $startedAt = microtime(true);
        $requestedAt = now();
        // Ghi log bắt đầu
        $logData = [
            'target_system'   => 'Casumina',
            'method'          => 'POST',
            'endpoint_url'    => $url,
            'request_headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'request_payload' => $payload,
        ];
        // Debug
        Log::info('Casumina API Request', [
            'method' => 'POST',
            'url' => $url,
            'payload' => $payload,
        ]);
        try {
            $response = $this->client()->send('POST', $url, [
                'body' => json_encode($payload),
            ]);
            // Debug
            Log::info('Casumina API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $responseHandle = $this->handleResponse($response, 'POST', $endpoint, $data);
            if (!$responseHandle) {
                $log = $this->logger->start(array_merge($logData, [
                    'requested_at' => $requestedAt
                ]));
                $this->logger->success($log, $response, $startedAt);
            }
            return $responseHandle;
        } catch (\Exception $e) {
            $log = $this->logger->start(array_merge($logData, [
                'requested_at' => $requestedAt
            ]));
            $this->logger->fail($log, $e, $startedAt);
            return $this->handleException($e, 'POST', $endpoint, $data);
        }
    }

    // /**
    //  * PUT request
    //  */
    // protected function put(string $endpoint = '', array $data = []): mixed
    // {
    //     try {
    //         $url = $this->apiUrl . $endpoint;
    //         $payload = $this->withCredentials($data);

    //         $response = $this->client()->send('PUT', $url, [
    //             'body' => json_encode($payload),
    //         ]);

    //         return $this->handleResponse($response, 'PUT', $endpoint, $data);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e, 'PUT', $endpoint, $data);
    //     }
    // }
    // /**
    //  * PATCH request
    //  */
    // protected function patch(string $endpoint = '', array $data = []): mixed
    // {
    //     try {
    //         $url = $this->apiUrl . $endpoint;
    //         $payload = $this->withCredentials($data);

    //         $response = $this->client()->send('PATCH', $url, [
    //             'body' => json_encode($payload),
    //         ]);

    //         return $this->handleResponse($response, 'PATCH', $endpoint, $data);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e, 'PATCH', $endpoint, $data);
    //     }
    // }

    // /**
    //  * DELETE request
    //  */
    // protected function delete(string $endpoint = '', array $data = []): mixed
    // {
    //     try {
    //         $url = $this->apiUrl . $endpoint;
    //         $payload = $this->withCredentials($data);

    //         $response = $this->client()->send('DELETE', $url, [
    //             'body' => json_encode($payload),
    //         ]);

    //         return $this->handleResponse($response, 'DELETE', $endpoint, $data);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e, 'DELETE', $endpoint, $data);
    //     }
    // }

    /**
     * Xử lý response
     */
    protected function handleResponse($response, string $method, string $endpoint, array $data): mixed
    {
        if ($response->successful()) {
            $result = $response->object();
            return empty((array) $result) ? null : $result;
        }

        Log::error('Casumina API error', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'body' => $response->body(),
            'request_data' => $data,
        ]);

        return null;
    }

    /**
     * Xử lý exception
     */
    protected function handleException(\Exception $e, string $method, string $endpoint, array $data): mixed
    {
        Log::error('Casumina API Exception', [
            'method' => $method,
            'endpoint' => $endpoint,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $data,
        ]);

        return null;
    }
}
