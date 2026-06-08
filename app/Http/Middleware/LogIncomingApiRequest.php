<?php

namespace App\Http\Middleware;

use App\Services\ApiRequestLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Providers\TelegramServiceProvider;
use Throwable;

class LogIncomingApiRequest
{
    public function __construct(
        protected ApiRequestLogger $logger
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_filter(explode(',', env('API_ALLOWED_IPS', '')));
        if (!empty($allowed) && !in_array($request->ip(), $allowed, true)) {
            $telegram = new TelegramServiceProvider();
            $telegram->sendMessage('Access denied. IP: ' . $request->ip() . ' - Allowed IPs: ' . implode(',', $allowed) . "\n\n" . 'Request: ' . $request->fullUrl());
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // $token = $request->bearerToken();
        // $expected = env('KEYSECRET_API', '');
        // if (!$token || !$expected || !hash_equals($expected, $token)) {
        //     $telegram = new TelegramServiceProvider();
        //     $telegram->sendMessage('Lỗi API key. Token: ' . $token . ' - Expected: ' . $expected . "\n\n" . 'Request: ' . $request->fullUrl());
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        $startedAt = microtime(true);

        $log = $this->safeStart($request);
        if ($log !== null) {
            app()->instance('api.request_id', $log->request_id);
        }
        try {
            /** @var Response $response */
            $response = $next($request);

            $this->safeFinish($log, $response, $startedAt);

            return $response;
        } catch (Throwable $e) {
            $this->safeFail($log, $e, $startedAt);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Safe wrappers — logger KHÔNG BAO GIỜ được làm crash request/response
    // -------------------------------------------------------------------------

    private function safeStart(Request $request): mixed
    {
        try {
            return $this->logger->start($request);
        } catch (Throwable $e) {
            Log::error('[ApiRequestLogger] start() failed', [
                'error' => $e->getMessage(),
                'url'   => $request->fullUrl(),
            ]);

            return null; // các bước sau đều guard null
        }
    }

    private function safeFinish(mixed $log, Response $response, float $startedAt): void
    {
        if ($log === null) return;

        try {
            $this->logger->finish($log, $response, $startedAt);
        } catch (Throwable $e) {
            Log::error('[ApiRequestLogger] finish() failed', [
                'error'   => $e->getMessage(),
                'log_id'  => $log->id ?? null,
            ]);
        }
    }

    private function safeFail(mixed $log, Throwable $e, float $startedAt): void
    {
        if ($log === null) return;

        try {
            $this->logger->fail($log, $e, $startedAt);
        } catch (Throwable $loggerException) {
            Log::error('[ApiRequestLogger] fail() failed', [
                'error'          => $loggerException->getMessage(),
                'original_error' => $e->getMessage(),
                'log_id'         => $log->id ?? null,
            ]);
        }
    }
}
