<?php

namespace App\Helpers;

class LanguageHelper
{
    /**
     * Lấy danh sách ngôn ngữ được hỗ trợ
     */
    public static function getSupportedLanguages(): array
    {
        return config('languages.supported', []);
    }

    /**
     * Lấy danh sách mã ngôn ngữ
     */
    public static function getLanguageCodes(): array
    {
        return array_keys(self::getSupportedLanguages());
    }

    /**
     * Lấy ngôn ngữ mặc định
     */
    public static function getDefaultLanguage(): string
    {
        return config('languages.default', 'vi');
    }

    /**
     * Kiểm tra ngôn ngữ có được hỗ trợ không
     */
    public static function isSupported(string $language): bool
    {
        return in_array($language, self::getLanguageCodes());
    }

    /**
     * Lấy tên hiển thị của ngôn ngữ
     */
    public static function getLanguageName(string $code): string
    {
        $languages = self::getSupportedLanguages();
        return $languages[$code] ?? $code;
    }

    /**
     * Lấy danh sách ngôn ngữ cho validation
     */
    public static function getValidationRule(): string
    {
        return 'in:' . implode(',', self::getLanguageCodes());
    }
}
