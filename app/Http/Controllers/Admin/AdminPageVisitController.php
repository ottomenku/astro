<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use App\Services\PageVisitAnalyticsService;
use App\Services\PageVisitRetentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPageVisitController extends Controller
{
    public function logs(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $visitorType = (string) $request->query('visitor_type', 'all');
        $days = max(1, min(90, (int) $request->query('days', 7)));
        $from = now()->subDays($days)->startOfDay();

        $visits = PageVisit::query()
            ->with('user')
            ->where('visited_at', '>=', $from)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('ip_address', 'like', "%{$q}%")
                        ->orWhere('user_name', 'like', "%{$q}%")
                        ->orWhere('user_email', 'like', "%{$q}%")
                        ->orWhere('page_label', 'like', "%{$q}%")
                        ->orWhere('path', 'like', "%{$q}%")
                        ->orWhere('route_name', 'like', "%{$q}%");
                });
            })
            ->when($visitorType === 'human', fn ($query) => $query->where('is_bot', false))
            ->when($visitorType === 'bot', fn ($query) => $query->where('is_bot', true))
            ->orderByDesc('visited_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.page-visits.logs', [
            'visits' => $visits,
            'q' => $q,
            'visitorType' => $visitorType,
            'days' => $days,
            'from' => $from,
            'retentionDays' => app(PageVisitRetentionService::class)->retentionDays(),
        ]);
    }

    public function summary(PageVisitAnalyticsService $analytics): View
    {
        return view('admin.page-visits.summary', [
            'report' => $analytics->summaryReport(),
            'retentionDays' => app(PageVisitRetentionService::class)->retentionDays(),
        ]);
    }

    public function updateSettings(Request $request, PageVisitRetentionService $retention): RedirectResponse
    {
        $validated = $request->validate([
            'retention_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $setting = $retention->updateRetentionDays((int) $validated['retention_days']);

        return redirect()
            ->route('admin.page-visits.summary')
            ->with('status', "Megőrzés beállítva: {$setting->retention_days} nap. A régebbi bejegyzések automatikusan törlődnek.");
    }
}
