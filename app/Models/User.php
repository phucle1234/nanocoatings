<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\TblNode;
use App\Models\TblNodeDownlineLogF1;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use CrudTrait, HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'code',
        'parent_code',
        'name',
        'user_name',
        'email',
        'password',
        'TokenID',
        'F1UserID',
        'role',
        'status',
        'is_active',
        'is_admin',
        'address',
        'phone',
        'avatar',
        'gender',
        'birthday',
        'channel',
        'zalo',
        'facebook',
        'city_code',
        'vehicle',
        'license_plate',
        'type',
        'city_name',
        'country',
        'latitude',
        'longitude',
        'link_map',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function generateUniqueToken()
    {
        do {
            $token = str_pad(random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (static::where('remember_token', $token)->exists());

        return $token;
    }

    // Relationship với tbl_node
    public function node()
    {
        return $this->hasOne(TblNode::class, 'UserID', 'id');
    }

    // Relationship với tbl_node_downline_log_f1
    public function downlineLogs()
    {
        return $this->hasMany(TblNodeDownlineLogF1::class, 'UserID', 'id');
    }

    public function f1DownlineLogs()
    {
        return $this->hasMany(TblNodeDownlineLogF1::class, 'FUserID', 'id');
    }

    /**
     * Quan hệ với đơn hàng
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Customers trực tiếp thuộc dealer/showroom này
     */
    public function customers()
    {
        return $this->hasMany(User::class, 'parent_id', 'id')
            ->where('role', 'customer');
    }

    // ========================================
    // DASHBOARD STATISTICS METHODS
    // ========================================

    /**
     * Lấy tổng số người dùng
     */
    public static function getTotalCount()
    {
        return self::count();
    }

    /**
     * Lấy số người dùng hoạt động
     */
    public static function getActiveCount()
    {
        return self::where('is_active', true)->count();
    }

    /**
     * Lấy số người dùng không hoạt động
     */
    public static function getInactiveCount()
    {
        return self::where('is_active', false)->count();
    }

    /**
     * Lấy số quản trị viên
     */
    public static function getAdminCount()
    {
        return self::where('is_admin', true)->count();
    }

    /**
     * Lấy số người dùng thường
     */
    public static function getRegularUserCount()
    {
        return self::where('is_admin', false)->count();
    }

    /**
     * Lấy số người dùng mới trong ngày
     */
    public static function getTodayCount()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Lấy số người dùng mới trong tuần
     */
    public static function getThisWeekCount()
    {
        return self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
    }

    /**
     * Lấy số người dùng mới trong tháng
     */
    public static function getThisMonthCount()
    {
        return self::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
    }

    /**
     * Lấy thống kê người dùng theo thời gian
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
     * Lấy người dùng có nhiều đơn hàng nhất
     */
    public function getOrderCount()
    {
        return $this->orders()->count();
    }

    /**
     * Lấy tổng giá trị đơn hàng của người dùng
     */
    public function getTotalOrderValue()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('total_amount');
    }

    /**
     * Lấy người dùng mới nhất
     */
    public static function getLatestUsers($limit = 10)
    {
        return self::orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Lấy người dùng có nhiều đơn hàng nhất
     */
    public static function getTopUsersByOrders($limit = 10)
    {
        return self::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Showroom con thuộc NPP này
     */
    public function showrooms()
    {
        return $this->hasMany(User::class, 'parent_id', 'id')
            ->where('role', 'dealer');
    }

    /**
     * NPP cha của showroom này
     */
    public function parentNpp()
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

    public function productCategories()
    {
        return $this->belongsToMany(
            \App\Models\ProductCategory::class,
            'npp_product_categories',   // bảng trung gian
            'user_id',                  // khóa ngoại trỏ về users
            'category_id'               // khóa ngoại trỏ về product_categories
        )->withTimestamps();
    }

    /**
     * Kiểm tra dealer này là cấp cha (NPP) hay không
     */
    public function isParentDealer(): bool
    {
        return $this->role === 'dealer'
            && empty($this->parent_id)
            && empty($this->parent_code);
    }

    /**
     * Kiểm tra dealer này là cấp con (Showroom) hay không
     */
    public function isChildDealer(): bool
    {
        return $this->role === 'dealer'
            && !empty($this->parent_id)
            && !empty($this->parent_code);
    }
}
