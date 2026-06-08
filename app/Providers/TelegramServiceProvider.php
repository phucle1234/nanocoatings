<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;

class TelegramServiceProvider
{
    protected $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    public function sendMessage($message, $chatId = null)
    {
        // Nếu không truyền chatId, dùng mặc định từ config
        $targetChatId = $chatId ?? config('services.telegram.chat_id');

        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $targetChatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);

        return $response->successful();
    }


    public function notifyApiRequest($title, $requestData, $response)
    {
        try {
            $message = $this->buildApiRequestMessage($title, $requestData, $response);
            $this->sendMessage($message);
        } catch (\Exception $e) {
            \Log::error('Telegram notification error: ' . $e->getMessage());
        }
    }

    /**
     * Build Traceability Message
     */
    private function buildApiRequestMessage($title, $requestData, $response)
    {
        $message = "🔍 <b>".$title."</b>\n";
        $message .= str_repeat("━", 25) . "\n\n";

        // Request Information
        $message .= "📤 <b>THÔNG TIN REQUEST:</b>\n";
        $message .= "<code>".json_encode($requestData)."</code>\n\n";

        // Response Information
        $message .= "📥 <b>PHẢN HỒI API:</b>\n";
        $message .= "<code>".json_encode($response)."</code>\n\n";

        $message .= "<code>Thời gian: " . now()->format('d/m/Y H:i:s') . "</code>\n\n";
        return $message;
    }
}
