<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_orders',
        'total_revenue',
        'average_order_value',
        'conversion_rate',
        'total_visitors',
        'total_carts',
    ];

    protected $casts = [
        'date' => 'date',
        'total_orders' => 'integer',
        'total_revenue' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'total_visitors' => 'integer',
        'total_carts' => 'integer',
    ];

    /**
     * Lấy hoặc tạo analytics cho ngày
     */
    public static function getOrCreate($date = null)
    {
        $date = $date ?: now()->toDateString();

        return static::firstOrCreate([
            'date' => $date,
        ], [
            'total_orders' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
            'conversion_rate' => 0,
            'total_visitors' => 0,
            'total_carts' => 0,
        ]);
    }

    /**
     * Cập nhật số đơn hàng
     */
    public function updateOrders($count, $revenue)
    {
        $this->increment('total_orders', $count);
        $this->increment('total_revenue', $revenue);

        // Cập nhật AOV
        if ($this->total_orders > 0) {
            $this->update([
                'average_order_value' => $this->total_revenue / $this->total_orders
            ]);
        }

        return $this;
    }

    /**
     * Cập nhật conversion rate
     */
    public function updateConversionRate($visitors, $carts, $orders)
    {
        $this->update([
            'total_visitors' => $visitors,
            'total_carts' => $carts,
            'total_orders' => $orders,
        ]);

        // Tính conversion rate
        if ($visitors > 0) {
            $this->update([
                'conversion_rate' => round(($orders / $visitors) * 100, 2)
            ]);
        }

        return $this;
    }

    /**
     * Lấy thống kê theo khoảng thời gian
     */
    public static function getStatsByDateRange($startDate, $endDate)
    {
        return static::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(total_orders) as total_orders,
                SUM(total_revenue) as total_revenue,
                AVG(average_order_value) as avg_order_value,
                AVG(conversion_rate) as avg_conversion_rate,
                SUM(total_visitors) as total_visitors,
                SUM(total_carts) as total_carts
            ')
            ->first();
    }

    /**
     * Lấy thống kê theo tháng
     */
    public static function getStatsByMonth($year, $month)
    {
        return static::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('
                SUM(total_orders) as total_orders,
                SUM(total_revenue) as total_revenue,
                AVG(average_order_value) as avg_order_value,
                AVG(conversion_rate) as avg_conversion_rate,
                SUM(total_visitors) as total_visitors,
                SUM(total_carts) as total_carts
            ')
            ->first();
    }

    /**
     * Lấy trend theo ngày
     */
    public static function getDailyTrend($days = 30)
    {
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();

        return static::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Lấy trend theo tháng
     */
    public static function getMonthlyTrend($months = 12)
    {
        $startDate = now()->subMonths($months)->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();

        return static::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                DATE_FORMAT(date, "%Y-%m") as month,
                SUM(total_orders) as total_orders,
                SUM(total_revenue) as total_revenue,
                AVG(average_order_value) as avg_order_value,
                AVG(conversion_rate) as avg_conversion_rate
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Lấy top ngày có doanh thu cao nhất
     */
    public static function getTopRevenueDays($limit = 10)
    {
        return static::orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy top ngày có nhiều đơn hàng nhất
     */
    public static function getTopOrderDays($limit = 10)
    {
        return static::orderBy('total_orders', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy ngày có conversion rate cao nhất
     */
    public static function getTopConversionDays($limit = 10)
    {
        return static::where('total_visitors', '>', 0)
            ->orderBy('conversion_rate', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope: Lấy theo ngày
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: Lấy theo khoảng thời gian
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: Lấy theo tháng
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    /**
     * Scope: Sắp xếp theo doanh thu
     */
    public function scopeOrderByRevenue($query, $direction = 'desc')
    {
        return $query->orderBy('total_revenue', $direction);
    }

    /**
     * Scope: Sắp xếp theo số đơn hàng
     */
    public function scopeOrderByOrders($query, $direction = 'desc')
    {
        return $query->orderBy('total_orders', $direction);
    }

    /**
     * Tạo báo cáo tổng hợp
     */
    public static function generateReport($startDate, $endDate)
    {
        $stats = static::getStatsByDateRange($startDate, $endDate);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1,
            ],
            'orders' => [
                'total' => $stats->total_orders ?? 0,
                'daily_average' => round(($stats->total_orders ?? 0) / ((\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1)), 2),
            ],
            'revenue' => [
                'total' => $stats->total_revenue ?? 0,
                'daily_average' => round(($stats->total_revenue ?? 0) / ((\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1)), 2),
            ],
            'performance' => [
                'average_order_value' => $stats->avg_order_value ?? 0,
                'conversion_rate' => $stats->avg_conversion_rate ?? 0,
                'total_visitors' => $stats->total_visitors ?? 0,
                'total_carts' => $stats->total_carts ?? 0,
            ],
        ];
    }
}
