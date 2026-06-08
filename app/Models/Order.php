<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'billing_address_id',
        'order_number',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'address',
        'shipped_at',
        'delivered_at',
        'type',
        'dealer_code',
        'cancel_reason',
        'qrcode',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'address' => 'object',
        'type' => 'string',
        'dealer_code' => 'string',
        'cancel_reason' => 'string',
        'qrcode' => 'string',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với OrderItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Quan hệ với địa chỉ giao hàng
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Quan hệ với địa chỉ thanh toán
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Quan hệ với OrderShipment
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(OrderShipment::class);
    }

    /**
     * Quan hệ với Payment
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Quan hệ với OrderStatusHistory
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Lấy tổng số lượng sản phẩm trong đơn hàng
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Lấy nhãn trạng thái với màu sắc
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning'],
            'processing' => ['label' => 'Đang xử lý', 'class' => 'info'],
            'shipped' => ['label' => 'Đã giao hàng', 'class' => 'primary'],
            'delivered' => ['label' => 'Đã nhận hàng', 'class' => 'success'],
            'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
        ];

        return $statuses[$this->status]['label'] ?? $this->status;
    }

    /**
     * Lấy class CSS cho trạng thái
     */
    public function getStatusClassAttribute(): string
    {
        $statuses = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ];

        return $statuses[$this->status] ?? 'secondary';
    }

    /**
     * Lấy nhãn trạng thái thanh toán
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        $statuses = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền',
            'cancelled' => 'Đã hủy',
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentMethodLabel(): string
    {
        $methods = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'credit_card' => 'Thẻ tín dụng',
            'paypal' => 'PayPal',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Lấy class CSS cho trạng thái thanh toán
     */
    public function getPaymentStatusClassAttribute(): string
    {
        $statuses = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'cancelled' => 'secondary',
        ];

        return $statuses[$this->payment_status] ?? 'secondary';
    }

    /**
     * Kiểm tra đơn hàng có thể hủy không
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Kiểm tra đơn hàng có thể cập nhật trạng thái không
     */
    public function canUpdateStatus(): bool
    {
        return $this->status !== 'cancelled';
    }

    /**
     * Scope: Lọc theo trạng thái
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Lọc theo trạng thái thanh toán
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope: Lọc theo ngày
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Lọc theo user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Tạo số đơn hàng tự động
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo order_number nếu chưa có
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    // ========================================
    // DASHBOARD STATISTICS METHODS
    // ========================================

    /**
     * Lấy tổng số đơn hàng
     */
    public static function getTotalCount()
    {
        return self::count();
    }

    /**
     * Lấy tổng doanh thu
     */
    public static function getTotalRevenue()
    {
        return self::where('payment_status', 'paid')->sum('total_amount');
    }

    /**
     * Lấy doanh thu theo tháng
     */
    public static function getMonthlyRevenue($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return self::where('payment_status', 'paid')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('total_amount');
    }

    /**
     * Lấy doanh thu theo ngày
     */
    public static function getDailyRevenue($date = null)
    {
        $date = $date ?? today();

        return self::where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->sum('total_amount');
    }

    /**
     * Lấy số đơn hàng theo trạng thái
     */
    public static function getCountByStatus($status)
    {
        return self::where('status', $status)->count();
    }

    /**
     * Lấy số đơn hàng chờ xử lý
     */
    public static function getPendingCount()
    {
        return self::where('status', 'pending')->count();
    }

    /**
     * Lấy số đơn hàng đang xử lý
     */
    public static function getProcessingCount()
    {
        return self::where('status', 'processing')->count();
    }

    /**
     * Lấy số đơn hàng đã giao
     */
    public static function getShippedCount()
    {
        return self::where('status', 'shipped')->count();
    }

    /**
     * Lấy số đơn hàng đã hoàn thành
     */
    public static function getDeliveredCount()
    {
        return self::where('status', 'delivered')->count();
    }

    /**
     * Lấy số đơn hàng đã hủy
     */
    public static function getCancelledCount()
    {
        return self::where('status', 'cancelled')->count();
    }

    /**
     * Lấy số đơn hàng trong ngày
     */
    public static function getTodayCount()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Lấy số đơn hàng trong tuần
     */
    public static function getThisWeekCount()
    {
        return self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
    }

    /**
     * Lấy số đơn hàng trong tháng
     */
    public static function getThisMonthCount()
    {
        return self::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
    }

    /**
     * Lấy thống kê đơn hàng theo thời gian
     */
    public static function getStatsByPeriod($period = 'month')
    {
        $data = [];

        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $date = now()->subHours($i);
                    $count = self::whereBetween('created_at', [
                        $date->copy()->startOfHour(),
                        $date->copy()->endOfHour()
                    ])->count();
                    $data[] = [
                        'label' => $date->format('H:i'),
                        'value' => $count
                    ];
                }
                break;

            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $count = self::whereDate('created_at', $date)->count();
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $count
                    ];
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $count = self::whereDate('created_at', $date)->count();
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $count
                    ];
                }
                break;

            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $count = self::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)->count();
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'value' => $count
                    ];
                }
                break;
        }

        return $data;
    }

    /**
     * Lấy thống kê doanh thu theo thời gian
     */
    public static function getRevenueStatsByPeriod($period = 'month')
    {
        $data = [];

        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $date = now()->subHours($i);
                    $revenue = self::where('payment_status', 'paid')
                        ->whereBetween('created_at', [
                            $date->copy()->startOfHour(),
                            $date->copy()->endOfHour()
                        ])->sum('total_amount');
                    $data[] = [
                        'label' => $date->format('H:i'),
                        'value' => $revenue
                    ];
                }
                break;

            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $revenue = self::where('payment_status', 'paid')
                        ->whereDate('created_at', $date)->sum('total_amount');
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $revenue
                    ];
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $revenue = self::where('payment_status', 'paid')
                        ->whereDate('created_at', $date)->sum('total_amount');
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $revenue
                    ];
                }
                break;

            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $revenue = self::where('payment_status', 'paid')
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)->sum('total_amount');
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'value' => $revenue
                    ];
                }
                break;
        }

        return $data;
    }

    /**
     * Lấy top khách hàng có nhiều đơn hàng nhất
     */
    public static function getTopCustomers($limit = 10)
    {
        return self::select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_spent'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('order_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy đơn hàng có giá trị cao nhất
     */
    public static function getHighestValueOrders($limit = 10)
    {
        return self::orderBy('total_amount', 'desc')
            ->with('user')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy trung bình giá trị đơn hàng
     */
    public static function getAverageOrderValue()
    {
        return self::where('payment_status', 'paid')->avg('total_amount');
    }
}
