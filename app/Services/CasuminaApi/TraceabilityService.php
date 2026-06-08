<?php

namespace App\Services\CasuminaApi;

use App\Providers\TelegramServiceProvider;
use Illuminate\Support\Facades\Log;

class TraceabilityService extends CasuminaApiClient
{
    /**
     * POST - Lấy thông tin truy nguyên sản phẩm theo mã QR code
     */
    public function getTraceability(string $qrcode)
    {
        $response = $this->post('/aprocess/qrcodeg/', ['qrcode' => $qrcode]);

        // Gửi Telegram notification
        try {
            $telegram = new TelegramServiceProvider();
            $telegram->notifyApiRequest('Call API Truy Nguyên', ['qrcode' => $qrcode], $response);
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
        return $response;
    }

    /**
     * POST - API đề nghị bảo hành sản phẩm
     */
    public function GetWarranty(array $filters)
    {
        $response = $this->post('/aprocess/warrantyr/', $filters);

        // Gửi Telegram notification
        try {
            $telegram = new TelegramServiceProvider();
            $telegram->notifyApiRequest('Call API đề nghị Bảo Hành', $filters, $response);
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
        return $response;
    }
}
