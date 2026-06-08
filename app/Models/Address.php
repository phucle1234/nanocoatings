<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy tên đầy đủ
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Lấy địa chỉ đầy đủ
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address_line_1;

        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }

        $address .= ', ' . $this->city;
        $address .= ', ' . $this->state;
        $address .= ' ' . $this->postal_code;
        $address .= ', ' . $this->country;

        return $address;
    }

    /**
     * Lấy địa chỉ ngắn gọn
     */
    public function getShortAddressAttribute()
    {
        return $this->address_line_1 . ', ' . $this->city . ', ' . $this->state;
    }

    /**
     * Đặt làm địa chỉ mặc định
     */
    public function setAsDefault()
    {
        // Bỏ default của các địa chỉ khác cùng type
        static::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->update(['is_default' => false]);

        // Đặt địa chỉ này làm default
        $this->update(['is_default' => true]);

        return $this;
    }

    /**
     * Scope: Lấy địa chỉ billing
     */
    public function scopeBilling($query)
    {
        return $query->where('type', 'billing');
    }

    /**
     * Scope: Lấy địa chỉ shipping
     */
    public function scopeShipping($query)
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Scope: Lấy địa chỉ mặc định
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Lấy địa chỉ mặc định theo type
     */
    public static function getDefault($userId, $type = 'shipping')
    {
        return static::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Tạo địa chỉ mới và đặt làm default
     */
    public static function createAndSetDefault(array $data)
    {
        // Bỏ default của địa chỉ cùng type
        static::where('user_id', $data['user_id'])
            ->where('type', $data['type'])
            ->update(['is_default' => false]);

        // Tạo địa chỉ mới với default = true
        $data['is_default'] = true;

        return static::create($data);
    }

    /**
     * Validate địa chỉ
     */
    public function isValid(): bool
    {
        return !empty($this->first_name) &&
            !empty($this->last_name) &&
            !empty($this->address_line_1) &&
            !empty($this->city) &&
            !empty($this->state) &&
            !empty($this->postal_code) &&
            !empty($this->country) &&
            !empty($this->phone);
    }

    /**
     * Format địa chỉ cho hiển thị
     */
    public function formatForDisplay()
    {
        $lines = [];

        if ($this->company) {
            $lines[] = $this->company;
        }

        $lines[] = $this->full_name;
        $lines[] = $this->address_line_1;

        if ($this->address_line_2) {
            $lines[] = $this->address_line_2;
        }

        $lines[] = $this->city . ', ' . $this->state . ' ' . $this->postal_code;
        $lines[] = $this->country;
        $lines[] = 'Tel: ' . $this->phone;

        return implode("\n", $lines);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Khi tạo địa chỉ mới, nếu là default thì bỏ default của các địa chỉ khác
        static::created(function ($address) {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('type', $address->type)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
