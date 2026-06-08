<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $table = 'npp_countries';

    protected $fillable = [
        'name_vi',
        'name_en',
        'code',
        'phone_code',
        'region',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id');
    }

    public function activeProvinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
