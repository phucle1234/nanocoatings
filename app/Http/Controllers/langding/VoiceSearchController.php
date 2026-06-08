<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Traits\ProductSearchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * VoiceSearchController
 * 
 * Xử lý unified search endpoint cho:
 * - Voice search (speech recognition)
 * - Text search (manual input)
 * - Dropdown search (car attributes, tire sizes)
 */
class VoiceSearchController extends Controller
{
    use ProductSearchable; // ✅ Import trait xử lý search logic
    /**
     * ✅ UNIFIED SEARCH ENDPOINT
     * Xử lý tất cả loại search: voice, text, car, size
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = trim($request->get('query'));
        $searchType = $request->get('type', 'text');
        $confidence = $request->get('confidence', 1.0);
        $vehicleType = $request->get('vehicleType', 'oto'); // ✅ Đảm bảo có dòng này

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'error' => 'Query parameter is required'
            ], 400);
        }

        try {
            // Log request với chi tiết
            Log::info('UNIFIED_SEARCH_REQUEST', [
                'original_query' => $query,
                'type' => $searchType,
                'confidence' => $confidence,
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // ✅ Gọi method từ Trait ProductSearchable
            $searchResults = $this->performSearch($query, $searchType, $confidence, $vehicleType);

            if (empty($searchResults)) {
                Log::info('SEARCH_NO_RESULTS', [
                    'query' => $query,
                    'type' => $searchType
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Không tìm thấy sản phẩm phù hợp',
                    'query' => $query,
                    'results' => [],
                    'total' => 0,
                    'search_type' => $searchType
                ]);
            }

            Log::info('SEARCH_SUCCESS', [
                'query' => $query,
                'type' => $searchType,
                'total_results' => count($searchResults)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tìm thấy sản phẩm phù hợp',
                'query' => $query,
                'results' => $searchResults,
                'total' => count($searchResults),
                'search_type' => $searchType
            ]);
        } catch (Exception $e) {
            Log::error('SEARCH_ERROR', [
                'query' => $query,
                'type' => $searchType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tìm kiếm',
                'query' => $query,
                'results' => [],
                'total' => 0,
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'search_type' => $searchType
            ], 500);
        }
    }

    // ℹ️ All search logic đã được chuyển sang ProductSearchable trait
    // ℹ️ Methods: performSearch(), searchByMeilisearch(), searchByExactAttributes(), 
    //            parseStructuredQuery(), queryProductsByAttributes(), 
    //            extractSearchKeywords(), formatSearchResults(), getAttributeValue()
}
