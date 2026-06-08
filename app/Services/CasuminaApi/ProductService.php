<?php

namespace App\Services\CasuminaApi;

class ProductService extends CasuminaApiClient
{
    /**
     * POST - Lấy giá sản phẩm
     * @param $itemNo: Mã sản phẩm
     * Ví dụ: "CASUMINA-001" hoặc "all" để lấy giá của tất cả sản phẩm
     */
    public function getProductPrice(string $itemNo)
    {
        return $this->post('/aprocess/priceg/', [
            'item_no' => $itemNo,
        ]);
    }

    public function getProductPriceBuyDealer(string $itemNo, string $dealerCode)
    {
        return $this->post('/aprocess/salepriceg/', [
            'item_no' => $itemNo,
            'source_code' => $dealerCode,
        ]);
    }

    public function getProductListBuyDealer(string $dealerCode)
    {
        $result =  $this->post('/aprocess/sourcecodeg/', [
            'source_code' => $dealerCode,
        ]);
        if (isset($result[0]->code)) {
            return $result[0]->item;
        }
        return [];
    }


    public function getProductByQrcode(string $dealerCode, string $qrcode)
    {
        return $this->post('/aprocess/qrsalepriceg/', [
            'source_code' => $dealerCode,
            'qrcode' => $qrcode,
        ]);
    }

    public function getProductStock(string $sourceCode, string $itemNo)
    {
        return $this->post('/aprocess/stockg/', [
            'source_code' => $sourceCode,
            'item_no' => $itemNo,
        ]);
    }
}
