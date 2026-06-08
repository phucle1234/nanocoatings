<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'review',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Kiểm tra review đã được approve
     */
    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /**
     * Approve review
     */
    public function approve()
    {
        $this->update(['is_approved' => true]);
        return $this;
    }

    /**
     * Reject review
     */
    public function reject()
    {
        $this->update(['is_approved' => false]);
        return $this;
    }

    /**
     * Tăng số lượt helpful
     */
    public function markAsHelpful()
    {
        $this->increment('helpful_count');
        return $this;
    }

    /**
     * Giảm số lượt helpful
     */
    public function markAsNotHelpful()
    {
        $this->decrement('helpful_count');
        return $this;
    }

    /**
     * Lấy tên user (ẩn danh nếu cần)
     */
    public function getUserDisplayNameAttribute()
    {
        if (!$this->user) {
            return 'Khách hàng';
        }

        // Có thể ẩn tên thật và chỉ hiển thị tên ẩn danh
        return $this->user->name ?? 'Khách hàng';
    }

    /**
     * Lấy rating dạng sao
     */
    public function getStarRatingAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Lấy rating dạng text
     */
    public function getRatingTextAttribute()
    {
        $ratings = [
            1 => 'Rất tệ',
            2 => 'Tệ',
            3 => 'Bình thường',
            4 => 'Tốt',
            5 => 'Rất tốt',
        ];

        return $ratings[$this->rating] ?? 'Không xác định';
    }

    /**
     * Scope: Lấy review đã approve
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: Lấy review chưa approve
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope: Lấy theo rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope: Lấy theo sản phẩm
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Lấy theo user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Sắp xếp theo helpful
     */
    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    /**
     * Scope: Sắp xếp theo rating cao
     */
    public function scopeHighestRating($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Scope: Sắp xếp theo rating thấp
     */
    public function scopeLowestRating($query)
    {
        return $query->orderBy('rating', 'asc');
    }

    /**
     * Lấy review của sản phẩm
     */
    public static function getProductReviews($productId, $limit = 10, $approved = true)
    {
        $query = static::forProduct($productId)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($approved) {
            $query->approved();
        }

        return $query->limit($limit)->get();
    }

    /**
     * Lấy rating trung bình của sản phẩm
     */
    public static function getAverageRating($productId)
    {
        return static::forProduct($productId)
            ->approved()
            ->avg('rating') ?? 0;
    }

    /**
     * Lấy số lượng review theo rating
     */
    public static function getRatingDistribution($productId)
    {
        return static::forProduct($productId)
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
    }

    /**
     * Lấy tổng số review của sản phẩm
     */
    public static function getTotalReviews($productId)
    {
        return static::forProduct($productId)
            ->approved()
            ->count();
    }

    /**
     * Kiểm tra user đã review sản phẩm chưa
     */
    public static function hasUserReviewed($userId, $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Tạo review mới
     */
    public static function createReview($productId, $userId, $rating, $title, $review, $orderId = null)
    {
        // Kiểm tra user đã review chưa
        if (static::hasUserReviewed($userId, $productId)) {
            throw new \Exception('Bạn đã đánh giá sản phẩm này rồi');
        }

        // Kiểm tra rating hợp lệ
        if ($rating < 1 || $rating > 5) {
            throw new \Exception('Rating phải từ 1 đến 5');
        }

        return static::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'rating' => $rating,
            'title' => $title,
            'review' => $review,
            'is_approved' => false, // Cần approve
            'helpful_count' => 0,
        ]);
    }
}
