<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reference_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ với User (người tạo)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy loại movement dạng text
     */
    public function getTypeTextAttribute()
    {
        $types = [
            'in' => 'Nhập hàng',
            'out' => 'Xuất hàng',
            'adjustment' => 'Điều chỉnh',
            'reserved' => 'Reserve hàng',
            'unreserved' => 'Release hàng',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Lấy màu sắc cho loại movement
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            'in' => 'success',
            'out' => 'danger',
            'adjustment' => 'warning',
            'reserved' => 'info',
            'unreserved' => 'secondary',
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Kiểm tra là nhập hàng
     */
    public function isStockIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Kiểm tra là xuất hàng
     */
    public function isStockOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Kiểm tra là điều chỉnh
     */
    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
    }

    /**
     * Kiểm tra là reserve
     */
    public function isReserved(): bool
    {
        return $this->type === 'reserved';
    }

    /**
     * Kiểm tra là release
     */
    public function isUnreserved(): bool
    {
        return $this->type === 'unreserved';
    }

    /**
     * Lấy reference object
     */
    public function getReference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = match ($this->reference_type) {
            'order' => Order::class,
            'cart' => Cart::class,
            'user' => User::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->reference_id);
    }

    /**
     * Scope: Lấy theo loại
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Lấy theo sản phẩm
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Lấy theo reference
     */
    public function scopeByReference($query, $type, $id)
    {
        return $query->where('reference_type', $type)
            ->where('reference_id', $id);
    }

    /**
     * Scope: Lấy theo người tạo
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Lấy theo khoảng thời gian
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Lấy lịch sử tồn kho của sản phẩm
     */
    public static function getProductHistory($productId, $limit = 50)
    {
        return static::forProduct($productId)
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy báo cáo tồn kho theo thời gian
     */
    public static function getInventoryReport($startDate, $endDate, $productId = null)
    {
        $query = static::byDateRange($startDate, $endDate);

        if ($productId) {
            $query->forProduct($productId);
        }

        return $query->selectRaw('
                product_id,
                type,
                SUM(CASE WHEN type = "in" THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN type = "out" THEN quantity ELSE 0 END) as total_out,
                SUM(CASE WHEN type = "reserved" THEN quantity ELSE 0 END) as total_reserved,
                SUM(CASE WHEN type = "unreserved" THEN quantity ELSE 0 END) as total_unreserved,
                COUNT(*) as movement_count
            ')
            ->groupBy('product_id', 'type')
            ->get();
    }

    /**
     * Tạo movement mới
     */
    public static function createMovement($productId, $type, $quantity, $notes = null, $referenceType = null, $referenceId = null, $userId = null)
    {
        return static::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $userId,
        ]);
    }
}
