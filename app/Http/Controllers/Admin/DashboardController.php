<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Hiển thị dashboard với các thống kê
     */
    public function index()
    {
        $stats = $this->getDashboardStats();

        return view('vendor.backpack.crud.operations.dashboard', compact('stats'));
    }

    /**
     * Lấy các thống kê cho dashboard
     */
    private function getDashboardStats()
    {
        return [
            // Thống kê tổng quan
            'total_products' => Product::getTotalCount(),
            'active_products' => Product::getActiveCount(),
            'total_orders' => Order::getTotalCount(),
            'total_users' => User::getTotalCount(),
            'total_categories' => ProductCategory::count(),

            // Thống kê doanh thu
            'total_revenue' => Order::getTotalRevenue(),
            'monthly_revenue' => Order::getMonthlyRevenue(),

            // Thống kê đơn hàng theo trạng thái
            'pending_orders' => Order::getPendingCount(),
            'processing_orders' => Order::getProcessingCount(),
            'shipped_orders' => Order::getShippedCount(),
            'delivered_orders' => Order::getDeliveredCount(),
            'cancelled_orders' => Order::getCancelledCount(),

            // Thống kê sản phẩm theo trạng thái
            'featured_products' => Product::getFeaturedCount(),
            'new_products' => Product::getNewCount(),
            'bestseller_products' => Product::getBestsellerCount(),
            'low_stock_products' => Product::getLowStockCount(),

            // Thống kê theo thời gian
            'orders_today' => Order::getTodayCount(),
            'orders_this_week' => Order::getThisWeekCount(),
            'orders_this_month' => Order::getThisMonthCount(),

            // Thống kê người dùng
            'active_users' => User::getActiveCount(),
            'admin_users' => User::getAdminCount(),
            'new_users_today' => User::getTodayCount(),
            'new_users_this_month' => User::getThisMonthCount(),
        ];
    }

    /**
     * API endpoint để lấy dữ liệu cho biểu đồ
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'orders');
        $period = $request->get('period', 'month'); // day, week, month, year

        switch ($type) {
            case 'orders':
                return $this->getOrdersChartData($period);
            case 'revenue':
                return $this->getRevenueChartData($period);
            case 'products':
                return $this->getProductsChartData($period);
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Dữ liệu biểu đồ đơn hàng
     */
    private function getOrdersChartData($period)
    {
        $data = Order::getStatsByPeriod($period);
        return response()->json($data);
    }

    /**
     * Dữ liệu biểu đồ doanh thu
     */
    private function getRevenueChartData($period)
    {
        $data = Order::getRevenueStatsByPeriod($period);
        return response()->json($data);
    }

    /**
     * Dữ liệu biểu đồ sản phẩm
     */
    private function getProductsChartData($period)
    {
        $data = Product::getStatsByPeriod($period);
        return response()->json($data);
    }
}
