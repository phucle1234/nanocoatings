<?php

namespace App\Traits;

use JsonException;
use Throwable;

trait SanitizesLogData
{
    protected function sanitizePayload(array $payload): array
    {
        $hiddenKeys = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'api_key',
            'apikey',
            'x_api_key',
            'secret',
            'client_secret',
            'client_password',
            'client_id',
            'secret_key',
            'private_key',
            'otp',
            'pin',
            'signature',
        ];

        $partialMaskKeys = [
            'email',
            'phone',
            'mobile',
            'full_name',
            'name',
            'address',
        ];

        array_walk_recursive($payload, function (&$value, $key) use ($hiddenKeys, $partialMaskKeys) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, $hiddenKeys, true)) {
                $value = '******';
                return;
            }

            if (in_array($normalizedKey, $partialMaskKeys, true) && is_scalar($value)) {
                $value = $this->maskPartial((string) $value);
                return;
            }

            if (is_string($value) && $this->looksLikeBearerToken($value)) {
                $value = 'Bearer ******';
                return;
            }
        });

        return $payload;
    }

    protected function safeEncode(mixed $data): ?string
    {
        if ($data === null) {
            return null;
        }

        try {
            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            return $this->truncateLargeText($json);
        } catch (JsonException | Throwable) {
            return null;
        }
    }

    protected function truncate(?string $text, int $max): ?string
    {
        if ($text === null) {
            return null;
        }

        return mb_strlen($text) > $max
            ? mb_substr($text, 0, $max)
            : $text;
    }

    protected function truncateLargeText(?string $text, int $max = 20000): ?string
    {
        if ($text === null) {
            return null;
        }

        return mb_strlen($text) > $max
            ? mb_substr($text, 0, $max) . '...[TRUNCATED]'
            : $text;
    }

    protected function maskPartial(string $value): string
    {
        $value = trim($value);
        $length = mb_strlen($value);

        if ($length <= 4) {
            return '****';
        }

        return mb_substr($value, 0, 2)
            . str_repeat('*', max(4, $length - 4))
            . mb_substr($value, -2);
    }

    protected function looksLikeBearerToken(string $value): bool
    {
        return str_starts_with(trim($value), 'Bearer ');
    }

    protected function shouldStoreResponsePayload(int $statusCode, ?string $routeName = null): bool
    {
        // Chỉ lưu response body khi lỗi, tránh phình DB
        if ($statusCode >= 400) {
            return true;
        }

        // Nếu muốn whitelist thêm route cần debug sâu thì mở ở đây
        $alwaysStoreRoutes = [
            // 'api.products.upsert',
        ];

        return $routeName !== null && in_array($routeName, $alwaysStoreRoutes, true);
    }
}
