<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CasuminaApi\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    private function _getDealerCodes(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isParentDealer()) {
            return array_merge($user->showrooms()->pluck('code')->toArray(), [$user->code]);
        }

        return [$user->code];
    }
    public function index()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'dashboard';
        $user = Auth::user();

        // Tính tổng doanh thu của đại lý
        $totalRevenue = Order::whereIn('dealer_code', $this->_getDealerCodes())->where('status', 5)->whereIn('type', ['customer', 'dealer_sale'])->sum('total_amount');
        // Tính tổng doanh thu của đại lý tháng trước
        $lastMonthRevenue = Order::whereIn('dealer_code', $this->_getDealerCodes())
            ->where('status', 5)
            ->whereIn('type', ['customer', 'dealer_sale'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_amount');
        // Tính tổng doanh thu của đại lý tháng này
        $currentMonthRevenue = Order::whereIn('dealer_code', $this->_getDealerCodes())
            ->where('status', 5)
            ->whereIn('type', ['customer', 'dealer_sale'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        // Tăng trưởng % doanh thu so với tháng trước
        $revenueGrowth = $lastMonthRevenue > 0 ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        // Tăng trưởng số tiền doanh thu so với tháng trước
        $revenueGrowthAmount = $this->dashboardService->formatMoneyShort($currentMonthRevenue - $lastMonthRevenue);

        // Tổng sản phẩm đã bán
        $totalProductsSold = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('dealer_code', $this->_getDealerCodes())
                ->where('status', 5)
                ->whereIn('type', ['customer', 'dealer_sale']);
        })
            ->sum('quantity');
        // Tổng sản phẩm đã bán hôm qua
        $productsSoldYesterday = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('dealer_code', $this->_getDealerCodes())
                ->where('status', 5)
                ->whereIn('type', ['customer', 'dealer_sale'])
                ->whereDate('created_at', now()->subDay()->toDateString());
        })
            ->sum('quantity');
        // Tổng sản phẩm đã bán hôm nay
        $productsSoldToday = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('dealer_code', $this->_getDealerCodes())
                ->where('status', 5)
                ->whereIn('type', ['customer', 'dealer_sale'])
                ->whereDate('created_at', now()->toDateString());
        })
            ->sum('quantity');
        // Tăng trưởng % sản phẩm đã bán so với hôm qua
        $productsSoldGrowth = $productsSoldYesterday > 0 ? (($productsSoldToday - $productsSoldYesterday) / $productsSoldYesterday) * 100 : 0;
        // Tăng trưởng số lượng sản phẩm đã bán so với hôm qua
        $productsSoldGrowthAmount = $productsSoldToday - $productsSoldYesterday;

        // Tổng số khách hàng, chỉ lấy ở bàng user có type = customer_info
        $totalCustomers = User::query()->where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code)->count();
        // Tổng số khách hàng hôm qua
        $customersYesterday = User::query()->where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code)->whereDate('created_at', now()->subDay()->toDateString())->count();
        // Tổng số khách hàng hôm nay
        $customersToday = User::query()->where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code)->whereDate('created_at', now()->toDateString())->count();
        // Tăng trưởng % khách hàng so với hôm qua
        $customersGrowth = $customersYesterday > 0 ? (($customersToday - $customersYesterday) / $customersYesterday) * 100 : 0;
        // Tăng trưởng số lượng khách hàng so với hôm qua
        $customersGrowthAmount = $customersToday - $customersYesterday;

        return view('dealer.layout.dashboard', compact('user', 'headerWhite', 'sidebarActive', 'totalRevenue', 'revenueGrowth', 'revenueGrowthAmount', 'totalProductsSold', 'productsSoldGrowth', 'productsSoldGrowthAmount', 'totalCustomers', 'customersGrowth', 'customersGrowthAmount'));
    }
}
