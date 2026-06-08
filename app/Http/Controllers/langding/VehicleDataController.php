<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Traits\CarSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleDataController extends Controller
{
    use CarSearch;

    /**
     * Lấy dữ liệu tìm kiếm theo loại xe
     * 
     * @param Request $request
     * @param string $vehicleType - 'xe-may', 'xe-tai', 'oto'
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehicleData(Request $request, string $vehicleType)
    {
        try {
            // Validate vehicle type
            $allowedTypes = ['xe-may', 'xe-tai', 'oto'];
            if (!in_array($vehicleType, $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid vehicle type. Allowed: ' . implode(', ', $allowedTypes)
                ], 400);
            }

            Log::info('VehicleData: Getting data for vehicle type', [
                'vehicle_type' => $vehicleType,
                'locale' => app()->getLocale()
            ]);

            // ✅ Lấy car search data từ FITMENTS TABLE (TỐI ƯU HƠN 10 LẦN!)
            $carSearchData = $this->getCarSearchDataFromFitments($vehicleType);

            // Lấy tire size data theo loại xe
            $tireSizeData = $this->getTireSizeDataByVehicleType($vehicleType);

            return response()->json([
                'success' => true,
                'data' => [
                    'carSearchData' => $carSearchData,
                    'tireSizeData' => $tireSizeData
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('VehicleData: Error getting vehicle data', [
                'vehicle_type' => $vehicleType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get vehicle data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
