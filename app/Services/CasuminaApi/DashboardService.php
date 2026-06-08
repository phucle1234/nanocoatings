<?php

namespace App\Services\CasuminaApi;

use App\Providers\TelegramServiceProvider;
use Illuminate\Support\Facades\Log;

class DashboardService extends CasuminaApiClient
{
    function formatMoneyShort($amount)
    {
        $negative = $amount < 0;
        $absAmount = abs($amount);
        $sign = $negative ? '-' : ($amount > 0 ? '+' : '');

        if ($absAmount >= 1000000000) {
            $value = $absAmount / 1000000000;
            $value = ($value == floor($value)) ? number_format($value, 0, ',', '.') : number_format($value, 1, ',', '.');
            return $sign . $value . ' Tỷ';
        }
        if ($absAmount >= 1000000) {
            $value = $absAmount / 1000000;
            $value = ($value == floor($value)) ? number_format($value, 0, ',', '.') : number_format($value, 1, ',', '.');
            return $sign . $value . ' Triệu';
        }
        if ($absAmount >= 1000) {
            $value = $absAmount / 1000;
            $value = ($value == floor($value)) ? number_format($value, 0, ',', '.') : number_format($value, 1, ',', '.');
            return $sign . $value . ' Nghìn';
        }
        return $sign . number_format($absAmount, 0, ',', '.') . 'đ';
    }
}
