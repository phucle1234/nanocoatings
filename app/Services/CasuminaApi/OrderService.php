<?php

namespace App\Services\CasuminaApi;

class OrderService extends CasuminaApiClient
{
    /**
     * Cập nhật trạng thái đơn hàng E-commerce lên Casumina
     */
    public function updateOrderEcommerceStatus(string $orderCode, $status, $reason = '')
    {
        return $this->post('/aprocess/ecomu/', [
            'order_no' => $orderCode,
            'status' => $status,
            'reason' => $reason,
        ]);
    }
}
