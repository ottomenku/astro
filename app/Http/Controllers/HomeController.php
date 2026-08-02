<?php

namespace App\Http\Controllers;

use App\Services\DailyHoroscopeService;
use App\Support\HoroscopePeriod;
use App\Support\SiteMode;
use App\Support\UiTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, DailyHoroscopeService $dailyHoroscope): View|RedirectResponse
    {
        if (SiteMode::isExpert()) {
            return redirect()->route('horoscope.index');
        }

        $period = HoroscopePeriod::normalize($request->query('period'));
        $message = null;
        $error = null;

        try {
            $message = $dailyHoroscope->forHomepage($request->user(), null, $period);
        } catch (\Throwable $exception) {
            report($exception);

            if ($exception instanceof \Illuminate\Database\QueryException
                && (str_contains($exception->getMessage(), 'Unknown column')
                    || str_contains($exception->getMessage(), "doesn't exist"))) {
                $error = __('horoscope.schema_outdated');
            } else {
                $error = __('daily.error');
            }
        }

        return UiTemplate::render('home', [
            'daily' => $message,
            'error' => $error,
            'period' => $period,
        ]);
    }
}
