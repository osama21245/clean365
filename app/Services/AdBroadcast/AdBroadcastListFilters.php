<?php

namespace App\Services\AdBroadcast;

use App\Models\AdBroadcast;
use Illuminate\Database\Eloquent\Builder;

class AdBroadcastListFilters
{
    public function apply(
        Builder $query,
        ?string $contentTarget = null,
        ?string $categoryId = null,
        ?string $zoneFilter = null,
        ?string $dateFilter = null,
    ): Builder {
        if ($contentTarget !== null && $contentTarget !== '') {
            $this->applyContentTarget($query, $contentTarget);
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        if ($zoneFilter !== null && $zoneFilter !== '') {
            $this->applyZone($query, $zoneFilter);
        }

        if ($dateFilter !== null && $dateFilter !== '') {
            $this->applyDate($query, $dateFilter);
        }

        return $query;
    }

    protected function applyContentTarget(Builder $query, string $contentTarget): void
    {
        if ($contentTarget === AdBroadcast::CONTENT_TARGET_NONE) {
            $query->where(function (Builder $q) {
                $q->where('content_target', AdBroadcast::CONTENT_TARGET_NONE)
                    ->orWhereNull('content_target')
                    ->orWhere('content_target', '');
            });

            return;
        }

        $query->where('content_target', $contentTarget);
    }

    protected function applyZone(Builder $query, string $zoneFilter): void
    {
        if ($zoneFilter === 'nationwide' || $zoneFilter === 'all_zones') {
            $query->where(function (Builder $q) {
                $q->whereNull('zone_ids')
                    ->orWhere('zone_ids', '[]');
            });

            return;
        }

        $query->whereJsonContains('zone_ids', $zoneFilter);
    }

    protected function applyDate(Builder $query, string $dateFilter): void
    {
        match ($dateFilter) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()]),
            'month' => $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month),
            default => null,
        };
    }
}
