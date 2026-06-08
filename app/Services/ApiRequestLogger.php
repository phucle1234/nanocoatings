<?php

namespace App\Services;

use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Traits\SanitizesLogData;

class ApiRequestLogger
{
    use SanitizesLogData;

    public function start(Request $request): ApiRequestLog
    {
        $requestId = $request->headers->get('X-Request-Id') ?: (string) str()->uuid();
        $request->attributes->set('api_request_log_request_id', $requestId);

        return ApiRequestLog::create([
            'request_id'       => $requestId,
            'source_system'    => $this->truncate($request->headers->get('X-Source-System'), 255),
            'endpoint'         => $this->truncate($request->path(), 500),
            'route_name'       => $this->truncate(optional($request->route())->getName(), 255),
            'method'           => $request->method(),
            'reference_type'   => $this->detectReferenceType($request),
            'reference_code'   => $this->truncate($this->detectReferenceCode($request), 255),
            'status'           => 'processing',
            'request_headers'  => $this->filterHeaders($request->headers->all()),
            'request_payload'  => $this->safeEncode($this->sanitizePayload($request->all())),
            'ip_address'       => $this->truncate((string) $request->ip(), 45),
            'user_agent'       => $this->truncate((string) $request->userAgent(), 500),
        ]);
    }

    public function finish(ApiRequestLog $log, Response $response, float $startedAt): void
    {
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $statusCode = $response->getStatusCode();
        $routeName = $log->route_name ?: null;

        $isSuccess = $statusCode >= 200 && $statusCode < 400;

        $responsePayload = null;
        if ($this->shouldStoreResponsePayload($statusCode, $routeName)) {
            $responsePayload = $this->truncateLargeText($this->safeResponseContent($response));
        }

        $log->update([
            'status'           => $isSuccess ? 'success' : 'failed',
            'error_message'    => !$isSuccess
                ? $this->truncate($this->extractErrorMessage($this->safeResponseContent($response)), 5000)
                : null,
            'http_status'      => $statusCode,
            'response_headers' => $this->filterHeaders($response->headers->allPreserveCaseWithoutCookies()),
            'response_payload' => $responsePayload,
            'duration_ms'      => $durationMs,
            'processed_at'     => now(),
        ]);
    }

    public function fail(ApiRequestLog $log, Throwable $e, float $startedAt): void
    {
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $log->update([
            'status'        => 'failed',
            'http_status'   => 500,
            'error_message' => $this->truncate($e->getMessage(), 5000),
            'duration_ms'   => $durationMs,
            'processed_at'  => now(),
        ]);
    }

    protected function extractErrorMessage(?string $body): string
    {
        if (empty($body)) {
            return 'HTTP error - empty response body';
        }

        $data = json_decode($body, true);

        if (is_array($data)) {
            foreach (['error_message', 'message', 'msg', 'description', 'error'] as $key) {
                if (!empty($data[$key])) {
                    return (string) $data[$key];
                }
            }
        }

        return 'HTTP error - body: ' . mb_substr($body, 0, 200);
    }

    protected function detectReferenceType(Request $request): ?string
    {
        if ($request->filled('item_no')) {
            return 'product';
        }

        if ($request->filled('customer_no')) {
            return 'customer';
        }

        if ($request->filled('username')) {
            return 'user';
        }

        if ($request->filled('dealer_code') || $request->filled('npp_code')) {
            return 'dealer';
        }

        if ($request->filled('order_code') || $request->filled('order_id')) {
            return 'order';
        }

        return null;
    }

    protected function detectReferenceCode(Request $request): ?string
    {
        foreach (
            [
                'item_no',
                'customer_no',
                'username',
                'dealer_code',
                'npp_code',
                'order_code',
                'order_id',
            ] as $key
        ) {
            $value = $request->input($key);

            if (!empty($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function filterHeaders(array $headers): array
    {
        $hiddenHeaders = [
            'authorization',
            'cookie',
            'set-cookie',
            'x-api-key',
            'x-access-token',
            'x-client-secret',
            'x-auth-token',
        ];

        $filtered = [];

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, $hiddenHeaders, true)) {
                $filtered[$key] = ['******'];
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function safeResponseContent(Response $response): ?string
    {
        try {
            $content = $response->getContent();

            if ($content === false) {
                return null;
            }

            return (string) $content;
        } catch (Throwable) {
            return null;
        }
    }
}
