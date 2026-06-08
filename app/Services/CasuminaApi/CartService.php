<?php

namespace App\Services\CasuminaApi;

use App\Providers\TelegramServiceProvider;
use Illuminate\Support\Facades\Log;

class CartService extends CasuminaApiClient
{
    /**
     * POST - Gửi thông tin giỏ hàng NPP cho Casumina
     *
     * @param string $username    Tên đăng nhập của NPP
     * @param array $shipInfo    Thông tin giao hàng {fullname, email, phone, address, city_code, note}
     * @param array  $listProduct Danh sách sản phẩm đặt hàng, mỗi phần tử gồm:
     *   [
     *     {
     *       'item_no'  => string,  // Mã sản phẩm
     *       'quantity' => int,     // Số lượng
     *       'price'    => float,   // Đơn giá
     *     },
     *     ...
     *   ]
     */
    public function checkout(string $username, $shipInfo = [], $listProduct = [])
    {
        $response = $this->post('/aprocess/ecomp/', [
            "username" => $username,
            "fullname" => $shipInfo['fullname'] ?? '',
            "email" => $shipInfo['email'] ?? '',
            "phone" => $shipInfo['phone'] ?? '',
            "address" => $shipInfo['address'] ?? '',
            "city_code" => $shipInfo['city_code'] ?? '',
            "note" => $shipInfo['note'] ?? '',
            "showroom" => $shipInfo['source_code'] ?? '',
            "order" => $listProduct
        ]);

        // Gửi Telegram notification
        try {
            $telegram = new TelegramServiceProvider();
            $telegram->notifyApiRequest('Call API User mua hàng (gửi thông tin cho NPP)', [
                "username" => $username,
                "fullname" => $shipInfo['fullname'] ?? '',
                "email" => $shipInfo['email'] ?? '',
                "phone" => $shipInfo['phone'] ?? '',
                "address" => $shipInfo['address'] ?? '',
                "city_code" => $shipInfo['city_code'] ?? '',
                "note" => $shipInfo['note'] ?? '',
                "order" => $listProduct
            ], $response);
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
        return $response;
    }


    public function checkoutBuy(string $username, $shipInfo = [], $listProduct = [])
    {
        return $this->post('/aprocess/placep/', [
            "username" => $username,
            "fullname" => $shipInfo['fullname'] ?? '',
            "email" => $shipInfo['email'] ?? '',
            "phone" => $shipInfo['phone'] ?? '',
            "address" => $shipInfo['address'] ?? '',
            "city_code" => $shipInfo['city_code'] ?? '',
            "note" => $shipInfo['note'] ?? '',
            "order" => $listProduct
        ]);
    }

    public function checkoutSale(string $username, string $customerNo, $listProduct = [])
    {
        return $this->post('/aprocess/salep/', [
            "username" => $username,
            "customer_no" => $customerNo,
            "order" => $listProduct
        ]);
    }

    public function checkoutLoan(string $username, string $dealerPartner, $listProduct = [])
    {
        return $this->post('/aprocess/borrowp/', [
            "username" => $username,
            "lend_source_code" => $dealerPartner,
            "order" => $listProduct
        ]);
    }
}
