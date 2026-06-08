<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    /**
     * Switch language and store in session
     * 
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, $locale)
    {
        // Validate locale (không fallback hardcode)
        $supportedLocales = array_keys(config('languages.supported', []));

        if (empty($supportedLocales)) {
            abort(500, 'No supported languages configured');
        }

        if (!in_array($locale, $supportedLocales)) {
            $locale = config('languages.default', $supportedLocales[0]);
        }

        // Store locale in session
        Session::put('locale', $locale);

        // Get the previous URL
        $previousUrl = url()->previous();

        // Parse the previous URL to remove old locale prefix if exists
        $path = parse_url($previousUrl, PHP_URL_PATH);

        // Lưu locale hiện tại để tìm translation
        $currentLocale = app()->getLocale();

        // Remove old locale prefix from path
        foreach ($supportedLocales as $oldLocale) {
            if (strpos($path, '/' . $oldLocale . '/') === 0) {
                $path = substr($path, strlen('/' . $oldLocale));
                break;
            }
            if ($path === '/' . $oldLocale) {
                $path = '/';
                break;
            }
        }

        // Xử lý chuyển đổi slug cho product, post, category
        $path = $this->translatePath($path, $currentLocale, $locale);

        // Redirect back to the same page (without locale prefix, middleware will handle it)
        return redirect($path ?: '/');
    }

    /**
     * Chuyển đổi path sang ngôn ngữ mới
     * Tìm translation slug tương ứng - config-driven, không hardcode
     */
    private function translatePath($path, $fromLocale, $toLocale)
    {
        // Lấy danh sách routes có translation từ config
        $translatableRoutes = config('translatable.routes', []);

        // Tự động detect nếu bật auto_detect
        $autoDetect = config('translatable.auto_detect', false);

        foreach ($translatableRoutes as $routePrefix => $config) {
            $pattern = '/^\/' . preg_quote($routePrefix, '/') . '\/(.+)$/';

            if (preg_match($pattern, $path, $matches)) {
                $slug = $matches[1];
                $newSlug = $this->getTranslatedSlug(
                    $config['table'],
                    $config['id_column'],
                    $slug,
                    $fromLocale,
                    $toLocale
                );

                if ($newSlug) {
                    return "/{$routePrefix}/{$newSlug}";
                }

                // Nếu không tìm thấy translation, giữ nguyên slug
                return $path;
            }
        }

        // Auto-detect: Nếu path match pattern /abc/{slug} và chưa có trong config
        if ($autoDetect && preg_match('/^\/([a-z\-]+)\/(.+)$/', $path, $matches)) {
            $routePrefix = $matches[1];
            $slug = $matches[2];

            // Convention: abc -> abc_translations, abc_id
            $table = $routePrefix . '_translations';
            $idColumn = $routePrefix . '_id';

            $newSlug = $this->getTranslatedSlug($table, $idColumn, $slug, $fromLocale, $toLocale);

            if ($newSlug) {
                return "/{$routePrefix}/{$newSlug}";
            }
        }

        return $path;
    }

    /**
     * Lấy slug translation tương ứng - Generic, không hardcode
     * 
     * @param string $table Translation table name
     * @param string $idColumn Foreign key column name (e.g. 'product_id')
     * @param string $currentSlug Current slug
     * @param string $fromLocale Current locale
     * @param string $toLocale Target locale
     * @return string|null
     */
    private function getTranslatedSlug($table, $idColumn, $currentSlug, $fromLocale, $toLocale)
    {
        try {
            // Kiểm tra table có tồn tại không
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                return null;
            }

            // Tìm record hiện tại
            $current = DB::table($table)
                ->where('slug', $currentSlug)
                ->where('language', $fromLocale)
                ->first();

            if (!$current) {
                return null;
            }

            // Lấy ID của entity (product_id, post_id, etc.)
            $entityId = $current->{$idColumn} ?? null;

            if (!$entityId) {
                return null;
            }

            // Tìm translation tương ứng với ngôn ngữ mới
            $translation = DB::table($table)
                ->where($idColumn, $entityId)
                ->where('language', $toLocale)
                ->first();

            return $translation ? $translation->slug : null;
        } catch (\Exception $e) {
            Log::error('Error translating slug', [
                'table' => $table,
                'id_column' => $idColumn,
                'slug' => $currentSlug,
                'from' => $fromLocale,
                'to' => $toLocale,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
