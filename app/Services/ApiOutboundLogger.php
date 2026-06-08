<?php

namespace App\Services;

use App\Models\ApiOutboundLog;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;
use App\Traits\SanitizesLogData;

class ApiOutboundLogger
{
    use SanitizesLogData;
    public function start(array $context): ApiOutboundLog
    {
        return ApiOutboundLog::create([
            'request_id' => $context['request_id']
                ?? (app()->bound('api.request_id') ? app('api.request_id') : null)
                ?? (string) str()->uuid(),
            'target_system'   => $context['target_system'] ?? null,
            'action'          => $context['action'] ?? null,
            'method'          => strtoupper($context['method'] ?? 'POST'),
            'endpoint_url'    => $context['endpoint_url'],
            'reference_type'  => $context['reference_type'] ?? null,
            'reference_code'  => $context['reference_code'] ?? null,
            'status'          => 'processing',
            'request_headers' => $this->sanitizeHeaders($context['request_headers'] ?? []),
            'request_payload' => $this->safeEncode(
                $this->sanitizePayload($context['request_payload'] ?? [])
            ),
            'attempt_no'      => (int) ($context['attempt_no'] ?? 1),
            'requested_at'    => now(),
        ]);
    }

    public function success(ApiOutboundLog $log, Response $response, float $startedAt, ?callable $successChecker = null): void
    {
        $this->completeWithResponse($log, $response, $startedAt, $successChecker);
    }

    public function fail(ApiOutboundLog $log, Throwable $e, float $startedAt): void
    {
        if ($e instanceof RequestException && $e->response !== null) {
            $this->completeWithResponse($log, $e->response, $startedAt);

            return;
        }

        $log->update([
            'status'        => 'failed',
            'http_status'   => null,
            'error_no'      => 'EXCEPTION',
            'error_message' => $this->truncate($e->getMessage(), 5000),
            'duration_ms'   => (int) round((microtime(true) - $startedAt) * 1000),
            'responded_at'  => now(),
        ]);
    }

    protected function completeWithResponse(ApiOutboundLog $log, Response $response, float $startedAt): void
    {
        $responseData = $response->json();

        $errorNo = is_array($responseData)
            ? (string) ($responseData['error_no'] ?? '')
            : '';

        $status = $this->isBusinessSuccess($responseData) ? 'success' : 'failed';

        $log->update([
            'status'           => $status,
            'http_status'      => $response->status(),
            'error_no'         => $errorNo !== '' ? $errorNo : null,
            'error_message'    => $status === 'failed'
                ? $this->extractErrorMessage($responseData)
                : null,
            'response_headers' => $this->sanitizeHeaders($response->headers()),
            'response_payload' => $this->truncateLargeText($response->body()),
            'duration_ms'      => (int) round((microtime(true) - $startedAt) * 1000),
            'responded_at'     => now(),
        ]);
    }

    protected function isBusinessSuccess(mixed $responseData, ?callable $checker = null): bool
    {
        if ($checker !== null) {
            return (bool) $checker($responseData);
        }

        $errorNo = $responseData['error_no'] ?? null;

        return $errorNo === '' || $errorNo === null;
    }

    protected function extractErrorMessage(mixed $responseData): ?string
    {
        if (!is_array($responseData)) {
            return 'Invalid response format';
        }

        foreach (['error_message', 'message', 'msg', 'description'] as $key) {
            if (!empty($responseData[$key])) {
                return $this->truncate((string) $responseData[$key], 5000);
            }
        }

        if (array_key_exists('error_no', $responseData) && $responseData['error_no'] !== '') {
            return 'Business error: ' . (string) $responseData['error_no'];
        }

        return null;
    }



    protected function sanitizeHeaders(array $headers): array
    {
        $hiddenHeaders = ['authorization', 'cookie', 'set-cookie'];

        $result = [];

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, $hiddenHeaders, true)) {
                $result[$key] = ['******'];
            } else {
                $result[$key] = is_array($value) ? $value : [$value];
            }
        }

        return $result;
    }
}
