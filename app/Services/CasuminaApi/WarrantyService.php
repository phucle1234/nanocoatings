<?php

namespace App\Services\CasuminaApi;

class WarrantyService extends CasuminaApiClient
{
    /**
     * POST - Lấy thông tin bảo hành sản phẩm theo QRcode
     */
    public function getWarrantyInfo(string $qrcode)
    {
        return $this->post('/aprocess/warrantyg/', [
            'qrcode' => $qrcode,
        ]);
    }

    /**
     * POST - Kích hoạt bảo hành sản phẩm theo QRcode
     */
    public function activateWarranty(string $orderCode, string $qrcode)
    {
        return $this->post('/aprocess/warrantyp/', [
            'order_no' => $orderCode,
            'qrcode' => $qrcode,
        ]);
    }

    /**
     * POST - Lưu thông tin QR bảo hành sản phẩm
     */
    public function saveWarrantyInfo(string $orderCode, string $qrcode)
    {
        return $this->post('/aprocess/qrcodep/', [
            'order_no' => $orderCode,
            'qrcode' => $qrcode,
        ]);
    }

    /**
     * POST - Đề nghị bảo hành sản phẩm / hóa đơn
     */
    public function requestWarranty(string $userName, array $data)
    {
        return $this->post('/aprocess/warrantysr/', [
            'username' => $userName,
            'fullname' => $data['fullname'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'order_no' => $data['order_no'] ?? null,
            'qrcode' => $data['qrcode'] ?? null,
            "content" => $data['content'] ?? null,
        ]);
    }
}
