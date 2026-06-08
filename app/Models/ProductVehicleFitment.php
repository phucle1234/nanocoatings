<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductVehicleFitment Model
 * 
 * Stores vehicle fitment information for products
 * Each record represents a specific vehicle that a product fits
 * 
 * @property int $id
 * @property int $product_id
 * @property string|null $manufacturer
 * @property string|null $model
 * @property string|null $year
 * @property string|null $trim
 * @property string|null $engine
 * @property bool $is_verified
 * @property string|null $notes
 */
class ProductVehicleFitment extends Model
{
    use CrudTrait;
    protected $fillable = [
        'product_id',
        'manufacturer',
        'model',
        'year',
        'trim',
        'engine',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    /**
     * Relationship với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: Tìm theo manufacturer
     */
    public function scopeForManufacturer($query, string $manufacturer)
    {
        return $query->where('manufacturer', $manufacturer);
    }

    /**
     * Scope: Tìm theo model
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope: Tìm theo year
     */
    public function scopeForYear($query, string $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope: Tìm theo manufacturer và model
     */
    public function scopeForManufacturerModel($query, string $manufacturer, string $model)
    {
        return $query->where('manufacturer', $manufacturer)
                    ->where('model', $model);
    }

    /**
     * Scope: Chỉ lấy verified
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Universal fit (no specific vehicle)
     */
    public function scopeUniversal($query)
    {
        return $query->whereNull('manufacturer')
                    ->whereNull('model')
                    ->whereNull('year');
    }

    /**
     * Get full vehicle name
     */
    public function getVehicleNameAttribute(): string
    {
        $parts = array_filter([
            $this->manufacturer,
            $this->model,
            $this->year,
            $this->trim,
        ]);

        return implode(' ', $parts) ?: 'Universal Fit';
    }

    /**
     * Check if this is a universal fitment
     */
    public function isUniversal(): bool
    {
        return is_null($this->manufacturer) 
            && is_null($this->model) 
            && is_null($this->year);
    }

    /**
     * Get fitment display string
     */
    public function getFitmentDisplayAttribute(): string
    {
        if ($this->isUniversal()) {
            return 'Universal Fit';
        }

        $display = $this->manufacturer ?? 'Any';
        
        if ($this->model) {
            $display .= ' ' . $this->model;
        } else {
            $display .= ' (All Models)';
        }
        
        if ($this->year) {
            $display .= ' ' . $this->year;
        }
        
        if ($this->trim) {
            $display .= ' ' . $this->trim;
        }

        return $display;
    }
}
