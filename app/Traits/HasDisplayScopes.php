<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasDisplayScopes
{
    /**
     * Lọc hiển thị theo trang chủ hoặc trang ngành (block Products / Bestseller).
     *
     * @param  int|null  $sectorId  null = trang chủ
     */
    public function scopeForDisplayBlock(Builder $query, ?int $sectorId = null): Builder
    {
        if ($sectorId === null) {
            return $query->where(function (Builder $q) {
                $q->where('display_scopes->homepage', true)
                    ->orWhereNull('display_scopes');
            });
        }

        return $query->whereJsonContains('display_scopes->sector_ids', $sectorId);
    }

    /**
     * @param  array<int|string>|null  $sectorIds
     * @return array{homepage: bool, sector_ids: array<int>}
     */
    public static function normalizeDisplayScopes(bool $homepage, ?array $sectorIds = null): array
    {
        $ids = array_values(array_unique(array_map(
            'intval',
            array_filter($sectorIds ?? [], fn ($id) => $id !== '' && $id !== null)
        )));

        return [
            'homepage' => $homepage,
            'sector_ids' => $ids,
        ];
    }
}
