<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'date',
        'views',
        'cart_additions',
        'orders',
        'revenue',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'cart_additions' => 'integer',
        'orders' => 'integer',
        'revenue' => 'decimal:2',
    ];

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViews($count = 1)
    {
        $this->increment('views', $count);
        return $this;
    }

    /**
     * Tăng lượt thêm vào giỏ hàng
     */
    public function incrementCartAdditions($count = 1)
    {
        $this->increment('cart_additions', $count);
        return $this;
    }

    /**
     * Tăng số đơn hàng
     */
    public function incrementOrders($count = 1, $revenue = 0)
    {
        $this->increment('orders', $count);
        $this->increment('revenue', $revenue);
        return $this;
    }

    /**
     * Lấy tỷ lệ chuyển đổi từ view sang cart
     */
    public function getViewToCartConversionRateAttribute()
    {
        if ($this->views == 0) {
            return 0;
        }

        return round(($this->cart_additions / $this->views) * 100, 2);
    }

    /**
     * Lấy tỷ lệ chuyển đổi từ cart sang order
     */
    public function getCartToOrderConversionRateAttribute()
    {
        if ($this->cart_additions == 0) {
            return 0;
        }

        return round(($this->orders / $this->cart_additions) * 100, 2);
    }

    /**
     * Lấy tỷ lệ chuyển đổi tổng thể
     */
    public function getOverallConversionRateAttribute()
    {
        if ($this->views == 0) {
            return 0;
        }

        return round(($this->orders / $this->views) * 100, 2);
    }

    /**
     * Lấy giá trị trung bình mỗi đơn hàng
     */
    public function getAverageOrderValueAttribute()
    {
        if ($this->orders == 0) {
            return 0;
        }

        return round($this->revenue / $this->orders, 2);
    }

    /**
     * Scope: Lấy theo sản phẩm
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
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
     * Scope: Sắp xếp theo lượt xem
     */
    public function scopeOrderByViews($query, $direction = 'desc')
    {
        return $query->orderBy('views', $direction);
    }

    /**
     * Scope: Sắp xếp theo doanh thu
     */
    public function scopeOrderByRevenue($query, $direction = 'desc')
    {
        return $query->orderBy('revenue', $direction);
    }

    /**
     * Lấy hoặc tạo analytics cho sản phẩm và ngày
     */
    public static function getOrCreate($productId, $date = null)
    {
        $date = $date ?: now()->toDateString();

        return static::firstOrCreate([
            'product_id' => $productId,
            'date' => $date,
        ], [
            'views' => 0,
            'cart_additions' => 0,
            'orders' => 0,
            'revenue' => 0,
        ]);
    }

    /**
     * Lấy thống kê sản phẩm theo khoảng thời gian
     */
    public static function getProductStats($productId, $startDate, $endDate)
    {
        return static::forProduct($productId)
            ->byDateRange($startDate, $endDate)
            ->selectRaw('
                SUM(views) as total_views,
                SUM(cart_additions) as total_cart_additions,
                SUM(orders) as total_orders,
                SUM(revenue) as total_revenue,
                AVG(views) as avg_views,
                AVG(cart_additions) as avg_cart_additions,
                AVG(orders) as avg_orders,
                AVG(revenue) as avg_revenue
            ')
            ->first();
    }

    /**
     * Lấy top sản phẩm theo metric
     */
    public static function getTopProducts($metric = 'views', $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::with(['product.translations']);

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw('product_id, SUM(' . $metric . ') as total_' . $metric)
            ->groupBy('product_id')
            ->orderBy('total_' . $metric, 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy trend của sản phẩm
     */
    public static function getProductTrend($productId, $days = 30)
    {
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();

        return static::forProduct($productId)
            ->byDateRange($startDate, $endDate)
            ->orderBy('date')
            ->get();
    }

    /**
     * Cập nhật analytics từ sự kiện
     */
    public static function recordEvent($productId, $event, $value = 1, $date = null)
    {
        $analytics = static::getOrCreate($productId, $date);

        switch ($event) {
            case 'view':
                $analytics->incrementViews($value);
                break;
            case 'cart_add':
                $analytics->incrementCartAdditions($value);
                break;
            case 'order':
                $analytics->incrementOrders($value, $value);
                break;
        }

        return $analytics;
    }
}
