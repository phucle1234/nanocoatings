<?php

namespace App\Services\CasuminaApi;

use App\Services\ApiOutboundLogger;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OutboundApiClient
{
    public function __construct(
        protected ApiOutboundLogger $logger
    ) {}

    public function send(array $options): array
    {
        $method = strtoupper($options['method'] ?? 'POST');
        $url = $options['url'];
        $payload = $options['payload'] ?? [];
        $headers = $options['headers'] ?? [];
        $timeout = (int) ($options['timeout'] ?? 60);

        $startedAt = microtime(true);

        $log = $this->logger->start([
            'request_id'      => $options['request_id'] ?? null,
            'target_system'   => $options['target_system'] ?? null,
            'action'          => $options['action'] ?? null,
            'method'          => $method,
            'endpoint_url'    => $url,
            'reference_type'  => $options['reference_type'] ?? null,
            'reference_code'  => $options['reference_code'] ?? null,
            'request_headers' => $headers,
            'request_payload' => $payload,
            'attempt_no'      => $options['attempt_no'] ?? 1,
        ]);

        try {
            $client = Http::timeout($timeout)
                ->withHeaders($headers)
                ->acceptJson();

            if (($options['as_json'] ?? true) === true) {
                $client = $client->asJson();
            }

            $response = match ($method) {
                'GET'    => $client->get($url, $payload),
                'POST'   => $client->post($url, $payload),
                'PUT'    => $client->put($url, $payload),
                'PATCH'  => $client->patch($url, $payload),
                'DELETE' => $client->delete($url, $payload),
                default  => throw new \InvalidArgumentException("Unsupported method: {$method}"),
            };

            $this->logger->success($log, $response, $startedAt);

            return [
                'ok'          => $this->determineSuccess($response->json(), $response->status(), $options),
                'http_status' => $response->status(),
                'data'        => $response->json(),
                'raw_body'    => $response->body(),
                'request_id'  => $log->request_id,
            ];
        } catch (RequestException $e) {
            $this->logger->fail($log, $e, $startedAt);

            return [
                'ok'          => false,
                'http_status' => optional($e->response)->status(),
                'data'        => optional($e->response)->json(),
                'raw_body'    => optional($e->response)->body(),
                'message'     => $e->getMessage(),
                'request_id'  => $log->request_id,
            ];
        } catch (Throwable $e) {
            $this->logger->fail($log, $e, $startedAt);

            return [
                'ok'          => false,
                'http_status' => null,
                'data'        => null,
                'raw_body'    => null,
                'message'     => $e->getMessage(),
                'request_id'  => $log->request_id,
            ];
        }
    }

    protected function determineSuccess(mixed $data, ?int $httpStatus, array $options): bool
    {
        if (!empty($options['success_checker']) && is_callable($options['success_checker'])) {
            return (bool) call_user_func($options['success_checker'], $data, $httpStatus);
        }

        return $httpStatus !== null && $httpStatus >= 200 && $httpStatus < 300;
    }
}
