<?php

namespace App\Services\CasuminaApi;

class ShowroomService extends CasuminaApiClient
{
    /**
     * POST - Lấy danh sách showroom
     */
    public function listShowrooms(array $filters)
    {
        return $this->post('/aprocess/showroomg/', $filters);
    }
    public function getSourceCodeList(array $filters)
    {
        $result = $this->post('/aprocess/sourcecodeg/', $filters);
        if (is_array($result)) {
            return $result;
        }
        // API trả về object { "data": [...] }
        return $result->data ?? [];
    }
    /**
     * POST - Lấy danh sách các quốc gia
     */
    public function listCountries(array $filters)
    {
        return $this->post('/aprocess/countryg/', $filters);
    }
}
