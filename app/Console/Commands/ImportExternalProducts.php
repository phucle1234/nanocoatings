<?php

namespace App\Console\Commands;

use App\Services\ExternalApiService;
use App\Services\ExternalCategoryImporter;
use App\Services\ExternalProductImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Import danh mục + sản phẩm từ API bên ngoài vào hệ thống.
 *
 * Usage:
 *   php artisan import:external                      # import categories + tất cả sản phẩm (xóa cũ trước)
 *   php artisan import:external --categories-only    # chỉ import danh mục
 *   php artisan import:external --category=01        # chỉ import 1 category (xóa cũ trước)
 *   php artisan import:external --skip-categories    # bỏ qua bước import danh mục
 *   php artisan import:external --sku=21060071       # sync 1 sản phẩm (không xóa dữ liệu cũ)
 */
class ImportExternalProducts extends Command
{
    protected $signature = 'import:external
                            {--categories-only : Chỉ import danh mục, bỏ qua sản phẩm}
                            {--skip-categories : Bỏ qua bước import danh mục}
                            {--category=       : Chỉ import sản phẩm của 1 category code (e.g. 01)}
                            {--sku=            : Sync 1 sản phẩm theo item_no/SKU (không xóa dữ liệu cũ, e.g. 21060071)}';

    protected $description = 'Import danh mục và sản phẩm từ API bên ngoài (maincsm.com)';

    public function __construct(
        private ExternalApiService       $api,
        private ExternalCategoryImporter $categoryImporter,
        private ExternalProductImporter  $productImporter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Kiểm tra cấu hình API (tránh lỗi mơ hồ trên server thiếu .env)
        $baseUrl  = config('services.casumina.url');
        $clientId = config('services.casumina.client_id');
        $clientPw = config('services.casumina.client_password');

        if (empty($baseUrl) || empty($clientId) || empty($clientPw)) {
            $missing = array_filter([
                empty($baseUrl) ? 'CASUMINA_API_URL' : null,
                empty($clientId) ? 'CASUMINA_CLIENT_ID' : null,
                empty($clientPw) ? 'CASUMINA_CLIENT_PASSWORD' : null,
            ]);
            $msg = 'Thiếu cấu hình API trong .env: ' . implode(', ', $missing)
                . '. Trên server nếu dùng php artisan config:cache thì chạy config:clear rồi cập nhật .env và chạy lại.';
            $this->error('  ❌ ' . $msg);
            Log::channel('import_external')->error('[Command] Cấu hình thiếu', [
                'missing' => $missing,
                'hint'    => 'Kiểm tra .env có CASUMINA_API_URL, CASUMINA_CLIENT_ID, CASUMINA_CLIENT_PASSWORD. Sau khi sửa .env chạy: php artisan config:clear',
            ]);
            return self::FAILURE;
        }

        Log::channel('import_external')->info('══════════════════════════════════════', [
            'command' => 'import:external',
            'options' => [
                'categories_only' => $this->option('categories-only'),
                'skip_categories'  => $this->option('skip-categories'),
                'category'        => $this->option('category'),
                'sku'             => $this->option('sku'),
            ],
            'api_base_url' => $baseUrl,
            'api_configured' => true,
        ]);

        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  Import dữ liệu từ API bên ngoài');
        $this->info('══════════════════════════════════════════════════');

        // ── Mode đặc biệt: --sku → sync 1 sản phẩm, không xóa dữ liệu ─────
        if ($this->option('sku')) {
            return $this->handleSingleSku();
        }

        // ── Bước 1: Import danh mục ──────────────────────────────────────
        $categories = [];

        if (!$this->option('skip-categories')) {
            $this->info('');
            $this->info('📂 Bước 1: Import danh mục...');

            try {
                $categories = $this->api->getCategories();
                $this->line('  → API trả về ' . count($categories) . ' danh mục');

                Log::channel('import_external')->info('[Command] Danh mục từ API', [
                    'count' => count($categories),
                    'list'  => array_map(fn ($c) => ['id' => $c['id'] ?? null, 'name' => $c['category'] ?? $c['name'] ?? null, 'has_sub' => !empty($c['sub'] ?? $c['groups'] ?? [])], $categories),
                ]);

                $stats = $this->categoryImporter->import($categories);
                $this->info("  ✅ Danh mục: tạo mới={$stats['created']}, cập nhật={$stats['updated']}");
            } catch (\Throwable $e) {
                $this->error('  ❌ Lỗi import danh mục: ' . $e->getMessage());
                Log::channel('import_external')->error('[Command] Lỗi import danh mục', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                return self::FAILURE;
            }
        } else {
            $this->warn('  ⏭  Bỏ qua bước import danh mục (--skip-categories)');

            // Vẫn cần lấy danh sách code để biết sẽ import product gì
            try {
                $categories = $this->api->getCategories();
            } catch (\Throwable $e) {
                $this->error('  ❌ Không thể lấy danh sách danh mục: ' . $e->getMessage());
                Log::channel('import_external')->error('[Command] Lỗi lấy danh mục API', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                return self::FAILURE;
            }
        }

        if ($this->option('categories-only')) {
            $this->info('');
            $this->info('✅ Hoàn tất (--categories-only).');
            return self::SUCCESS;
        }

        // ── Xóa toàn bộ sản phẩm cũ (trước khi import lại) ─────────────────
        $this->info('');
        $this->info('🗑  Xóa toàn bộ sản phẩm cũ...');

        try {
            $countBefore = DB::table('products')->count();
            DB::table('products')->delete();
            Log::channel('import_external')->info('[Command] Đã xóa toàn bộ sản phẩm', [
                'deleted' => $countBefore,
            ]);
            $this->line("  → Đã xóa {$countBefore} sản phẩm (và dữ liệu liên quan: translations, fitments, attributes pivot, category pivot).");
        } catch (\Throwable $e) {
            $this->error('  ❌ Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            Log::channel('import_external')->error('[Command] Lỗi xóa sản phẩm', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return self::FAILURE;
        }

        // ── Bước 2: Import sản phẩm ──────────────────────────────────────
        $this->info('');
        $this->info('📦 Bước 2: Import sản phẩm...');

        $filterCode = $this->option('category');

        // Lấy map code → id sau khi import categories
        $codeMap = $this->categoryImporter->getCodeToIdMap();
        Log::channel('import_external')->info('[Command] Map code → id danh mục (dùng cho sản phẩm)', [
            'count' => count($codeMap),
            'codes' => array_keys($codeMap),
            'map'   => $codeMap,
        ]);
        $productImporter = new ExternalProductImporter($codeMap, $this->api);

        $totalCreated  = 0;
        $totalUpdated  = 0;
        $totalSkipped  = 0;
        $totalFitments = 0;

        foreach ($categories as $cat) {
            $code = (string) ($cat['id'] ?? '');
            $name = (string) ($cat['category'] ?? $cat['name'] ?? $code);

            if ($code === '') {
                continue;
            }

            // Lọc theo --category nếu có
            if ($filterCode && $filterCode !== $code) {
                continue;
            }

            $this->line('');
            $this->line("  ─── [{$code}] {$name} ───");

            try {
                $products = $this->api->getProductsByCategory($code);
                $this->line("    → API trả về " . count($products) . " sản phẩm");

                $stats = $productImporter->import($products, $code);

                $this->info("    ✅ Tạo: {$stats['created']} | Cập nhật: {$stats['updated']} | Bỏ qua: {$stats['skipped']} | Fitments: {$stats['fitments']}");

                $totalCreated  += $stats['created'];
                $totalUpdated  += $stats['updated'];
                $totalSkipped  += $stats['skipped'];
                $totalFitments += $stats['fitments'];

            } catch (\Throwable $e) {
                $this->error("    ❌ Lỗi category [{$code}]: " . $e->getMessage());
                Log::channel('import_external')->error('[Command] Lỗi import sản phẩm theo category', [
                    'category' => $code,
                    'message'  => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ]);
            }
        }

        // ── Đổi mã danh mục gốc 01/03/04 sang slug (sau khi import xong) ───
        $this->info('');
        $this->info('🏷  Đổi mã danh mục: 01→sam-lop-xe-tai, 03→sam-lop-xe-may, 04→lop-advenza-pcr...');

        $codeRenameMap = [
            '01' => 'sam-lop-xe-tai',
            '03' => 'sam-lop-xe-may',
            '04' => 'lop-advenza-pcr',
        ];
        // try {
        //     foreach ($codeRenameMap as $from => $to) {
        //         $updated = DB::table('product_categories')->where('code', $from)->update(['code' => $to, 'updated_at' => now()]);
        //         if ($updated > 0) {
        //             $this->line("  → {$from} → {$to}");
        //             Log::channel('import_external')->info('[Command] Đổi mã danh mục', ['from' => $from, 'to' => $to]);
        //         }
        //     }
        // } catch (\Throwable $e) {
        //     $this->warn('  ⚠ Không đổi được mã danh mục: ' . $e->getMessage());
        //     Log::channel('import_external')->warning('[Command] Lỗi đổi mã danh mục', ['error' => $e->getMessage()]);
        // }

        // ── Tổng kết ─────────────────────────────────────────────────────
        Log::channel('import_external')->info('[Command] Kết thúc import', [
            'created'  => $totalCreated,
            'updated'  => $totalUpdated,
            'skipped'  => $totalSkipped,
            'fitments' => $totalFitments,
        ]);

        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info("  ✅ Hoàn tất!");
        $this->info("  Sản phẩm tạo mới : {$totalCreated}");
        $this->info("  Sản phẩm cập nhật: {$totalUpdated}");
        $this->info("  Bỏ qua           : {$totalSkipped}");
        $this->info("  Vehicle fitments : {$totalFitments}");
        $this->info('══════════════════════════════════════════════════');
        $this->line('  Log chi tiết: storage/logs/import-external.log');

        // Xóa cache Vehicle Search + Tire size để dropdown gợi ý lấy dữ liệu mới
        try {
            app(\App\Http\Controllers\langding\VehicleDataController::class)->clearFitmentsSearchCache();
            $this->line('  Đã xóa cache Vehicle Search & quy cách (dropdown sẽ cập nhật khi load lại).');
        } catch (\Throwable $e) {
            Log::channel('import_external')->warning('[Command] Clear cache thất bại', ['error' => $e->getMessage()]);
        }

        return self::SUCCESS;
    }

    /**
     * Sync 1 sản phẩm theo SKU/item_no mà không xóa dữ liệu cũ.
     * Sản phẩm đã tồn tại sẽ được cập nhật, chưa có sẽ được tạo mới.
     */
    private function handleSingleSku(): int
    {
        $sku = trim((string) $this->option('sku'));
        $this->info('');
        $this->info("🔄 Sync sản phẩm: {$sku}");

        // Lấy dữ liệu sản phẩm từ API
        try {
            $item = $this->api->getProductByItemNo($sku);
        } catch (\Throwable $e) {
            $this->error('  ❌ Lỗi gọi API: ' . $e->getMessage());
            Log::channel('import_external')->error('[Command] --sku: lỗi gọi API', ['sku' => $sku, 'error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if ($item === null) {
            $this->error("  ❌ API không trả về sản phẩm với item_no={$sku}");
            Log::channel('import_external')->warning('[Command] --sku: không tìm thấy sản phẩm', ['sku' => $sku]);
            return self::FAILURE;
        }

        $categoryCode = (string) ($item['item_category_code'] ?? '');
        if ($categoryCode === '') {
            $this->error("  ❌ Sản phẩm không có item_category_code, không xác định được danh mục.");
            return self::FAILURE;
        }

        $this->line("  → item_no={$sku} | item_category_code={$categoryCode}");

        // Lấy map code → id (không import lại categories)
        $codeMap = $this->categoryImporter->getCodeToIdMap();
        $productImporter = new ExternalProductImporter($codeMap, $this->api);

        try {
            $stats = $productImporter->import([$item], $categoryCode);
        } catch (\Throwable $e) {
            $this->error('  ❌ Lỗi import sản phẩm: ' . $e->getMessage());
            Log::channel('import_external')->error('[Command] --sku: lỗi import', ['sku' => $sku, 'error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if ($stats['skipped'] > 0) {
            $this->warn("  ⚠  Sản phẩm bị bỏ qua (item_no hoặc item_name rỗng).");
            return self::FAILURE;
        }

        $action = $stats['created'] > 0 ? '✅ Tạo mới' : '🔄 Cập nhật';
        $this->info("  {$action} thành công | Fitments: {$stats['fitments']}");
        Log::channel('import_external')->info('[Command] --sku: hoàn tất', [
            'sku'      => $sku,
            'created'  => $stats['created'],
            'updated'  => $stats['updated'],
            'fitments' => $stats['fitments'],
        ]);

        // Xóa cache dropdown
        try {
            app(\App\Http\Controllers\langding\VehicleDataController::class)->clearFitmentsSearchCache();
        } catch (\Throwable $e) {
            Log::channel('import_external')->warning('[Command] --sku: clear cache thất bại', ['error' => $e->getMessage()]);
        }

        return self::SUCCESS;
    }
}
