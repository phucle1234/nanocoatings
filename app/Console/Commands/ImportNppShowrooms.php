<?php

namespace App\Console\Commands;

use App\Services\NppShowroomImporter;
use Illuminate\Console\Command;

/**
 * Đồng bộ toàn bộ showroom/NPP từ API Casumina (giống POST /api/webhook/npp-insert).
 *
 * Usage:
 *   php artisan npp:import-showrooms
 */
class ImportNppShowrooms extends Command
{
    protected $signature = 'npp:import-showrooms';

    protected $description = 'Import toàn bộ showroom/NPP từ API Casumina (showroom=all)';

    public function __construct(
        private readonly NppShowroomImporter $nppShowroomImporter
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $baseUrl  = config('services.casumina.url');
        $clientId = config('services.casumina.client_id');
        $clientPw = config('services.casumina.client_password');

        if (empty($baseUrl) || empty($clientId) || empty($clientPw)) {
            $missing = array_filter([
                empty($baseUrl) ? 'CASUMINA_API_URL' : null,
                empty($clientId) ? 'CASUMINA_CLIENT_ID' : null,
                empty($clientPw) ? 'CASUMINA_CLIENT_PASSWORD' : null,
            ]);
            $this->error('Thiếu cấu hình: ' . implode(', ', $missing));

            return self::FAILURE;
        }

        $this->info('Đang gọi API showroom (showroom=all)...');

        $result = $this->nppShowroomImporter->importAll();

        if (!($result['success'] ?? false)) {
            $this->error($result['message'] ?? 'Import thất bại');
            if (!empty($result['error'])) {
                $this->line($result['error']);
            }

            return self::FAILURE;
        }

        $data = $result['data'] ?? [];
        $this->info($result['message'] ?? 'Hoàn tất');
        $this->table(
            ['Chỉ số', 'Giá trị'],
            [
                ['total', $data['total'] ?? '—'],
                ['parent_created', $data['parent_created'] ?? '—'],
                ['parent_updated', $data['parent_updated'] ?? '—'],
                ['showroom_created', $data['showroom_created'] ?? '—'],
                ['showroom_updated', $data['showroom_updated'] ?? '—'],
                ['customers_created', $data['customers_created'] ?? '—'],
                ['customers_updated', $data['customers_updated'] ?? '—'],
                ['customers_skipped', $data['customers_skipped'] ?? '—'],
                ['customer_sync_parents', $data['customer_sync_parents'] ?? '—'],
                ['failed', $data['failed'] ?? '—'],
            ]
        );

        $errors = $data['errors'] ?? [];
        if (is_array($errors) && count($errors) > 0) {
            $this->warn('Có ' . count($errors) . ' bản ghi lỗi (xem log chi tiết).');
        }

        $custErr = $data['customer_import_errors'] ?? [];
        if (is_array($custErr) && count($custErr) > 0) {
            $this->warn('Import khách từ customerg: ' . count($custErr) . ' lỗi dòng (xem log).');
        }

        return self::SUCCESS;
    }
}
