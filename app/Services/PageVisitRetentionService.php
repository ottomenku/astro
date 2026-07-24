<?php

namespace App\Services;

use App\Models\AnalyticsSetting;
use App\Models\PageVisit;
use Illuminate\Support\Carbon;

class PageVisitRetentionService
{
    public function retentionDays(): int
    {
        return AnalyticsSetting::current()->retention_days;
    }

    public function updateRetentionDays(int $days): AnalyticsSetting
    {
        $days = max(AnalyticsSetting::MIN_RETENTION_DAYS, min(AnalyticsSetting::MAX_RETENTION_DAYS, $days));

        $setting = AnalyticsSetting::current();
        $setting->update(['retention_days' => $days]);
        $this->purgeOlderThan($days);

        return $setting->fresh();
    }

    public function purgeExpired(): int
    {
        return $this->purgeOlderThan($this->retentionDays());
    }

    public function purgeOlderThan(int $days): int
    {
        $cutoff = Carbon::now()->subDays($days);

        return PageVisit::query()
            ->where('visited_at', '<', $cutoff)
            ->delete();
    }
}
