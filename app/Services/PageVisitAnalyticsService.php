<?php

namespace App\Services;

use App\Models\AnalyticsSetting;
use App\Models\PageVisit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PageVisitAnalyticsService
{
    /**
     * @return array{
     *     from: Carbon,
     *     to: Carbon,
     *     total: int,
     *     humans: int,
     *     bots: int,
     *     unique_ips: int
     * }
     */
    public function overview(Carbon $from, Carbon $to): array
    {
        $base = PageVisit::query()
            ->whereBetween('visited_at', [$from, $to]);

        return [
            'from' => $from,
            'to' => $to,
            'total' => (clone $base)->count(),
            'humans' => (clone $base)->where('is_bot', false)->count(),
            'bots' => (clone $base)->where('is_bot', true)->count(),
            'unique_ips' => (clone $base)->distinct('ip_address')->count('ip_address'),
        ];
    }

    /**
     * @return Collection<int, object{
     *     route_name: ?string,
     *     page_label: ?string,
     *     hits: int,
     *     unique_ips: int,
     *     human_hits: int,
     *     bot_hits: int
     * }>
     */
    public function pageBreakdown(Carbon $from, Carbon $to): Collection
    {
        return PageVisit::query()
            ->select([
                'route_name',
                'page_label',
                DB::raw('COUNT(*) as hits'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_ips'),
                DB::raw('SUM(CASE WHEN is_bot = 0 THEN 1 ELSE 0 END) as human_hits'),
                DB::raw('SUM(CASE WHEN is_bot = 1 THEN 1 ELSE 0 END) as bot_hits'),
            ])
            ->whereBetween('visited_at', [$from, $to])
            ->groupBy('route_name', 'page_label')
            ->orderByDesc('hits')
            ->get();
    }

    /**
     * @return array{
     *     week: array{label: string, from: Carbon, to: Carbon, rows: Collection, overview: array},
     *     month: array{label: string, from: Carbon, to: Carbon, rows: Collection, overview: array},
     *     retention_days: int
     * }
     */
    public function summaryReport(): array
    {
        $timezone = config('app.timezone', 'Europe/Budapest');
        $now = now($timezone);
        $retentionDays = AnalyticsSetting::current()->retention_days;

        $weekFrom = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekTo = $now->copy()->endOfDay();

        $monthFrom = $now->copy()->startOfMonth()->startOfDay();
        $monthTo = $now->copy()->endOfDay();

        return [
            'retention_days' => $retentionDays,
            'week' => [
                'label' => sprintf('%s – %s', $weekFrom->format('Y.m.d'), $weekTo->format('Y.m.d')),
                'from' => $weekFrom,
                'to' => $weekTo,
                'rows' => $this->pageBreakdown($weekFrom, $weekTo),
                'overview' => $this->overview($weekFrom, $weekTo),
            ],
            'month' => [
                'label' => $now->format('Y. F'),
                'from' => $monthFrom,
                'to' => $monthTo,
                'rows' => $this->pageBreakdown($monthFrom, $monthTo),
                'overview' => $this->overview($monthFrom, $monthTo),
            ],
        ];
    }
}
