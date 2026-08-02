<?php

namespace App\Http\Controllers;

use App\Support\HoroscopePeriod;
use App\Support\SiteMode;
use App\Support\UiTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalMessageController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (SiteMode::isExpert()) {
            return redirect()->route('horoscope.index', ['view' => 'daily']);
        }

        $user = $request->user();
        if (! $user) {
            return redirect()->route('home', ['auth' => 'login']);
        }

        $birthCharts = $user->birthCharts()->orderByDesc('is_default')->orderBy('name')->get();
        $period = HoroscopePeriod::normalize($request->query('period'));
        $defaultChart = $birthCharts->firstWhere('is_default', true) ?? $birthCharts->first();

        return UiTemplate::render('personal-message', [
            'birthCharts' => $birthCharts,
            'birthChartsJson' => $birthCharts->map(fn ($chart) => [
                'id' => $chart->id,
                'name' => $chart->name,
                'is_default' => $chart->is_default,
            ])->values(),
            'hasBirthChart' => $birthCharts->isNotEmpty(),
            'defaultBirthChartId' => $defaultChart?->id,
            'period' => $period,
        ]);
    }
}
