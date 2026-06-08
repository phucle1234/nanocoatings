<?php

namespace App\Services\CasuminaApi;

class CustomerService extends CasuminaApiClient
{
    /**
     * POST - Đăng ký tài khoản mới
     */
    public function createCustomer(array $customerData)
    {
        return $this->post('/aprocess/accountp/', $customerData);
    }
    public function updateCustomer(array $customerData)
    {
        return $this->post('/aprocess/accountu/', $customerData);
    }
    public function resetPassword(array $customerData)
    {
        return $this->post('/aprocess/resetpass/', $customerData);
    }

    public function contactCustomer(array $data)
    {
        return $this->post('/aprocess/contactp/', $data);
    }
    /**
     * GET - Lấy danh sách khách hàng theo NPP
     */
    public function getCustomersByDealer(string $dealerCode)
    {
        $result =  $this->post('/aprocess/sourcecodeg/', [
            'source_code' => $dealerCode,
        ]);
        if (isset($result[0]->code)) {
            return $result[0]->customer;
        }
        return [];
    }

    /**
     * GET - Tạo khách hàng mới theo NPP (Khách hàng không có tài khoản)
     */
    public function createCustomerByDealer(string $dealerCode, array $data)
    {
        $result =  $this->post('/aprocess/customerp/', [
            'source_code' => $dealerCode,
            'customer_name' => $data['name'] ?? '',
            'gender' => $data['gender'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'zalo' => $data['zalo'] ?? '',
            'facebook' => $data['facebook'] ?? '',
            'vehicle' => $data['vehicle'] ?? '',
            'license_plate' => $data['license_plate'] ?? '',
            'address' => $data['address'] ?? '',
            'city_code' => $data['city'] ?? '',
        ]);
        if (isset($result->error_no) && $result->error_no != '') {
            return null;
        }
        return $result;
    }
}
