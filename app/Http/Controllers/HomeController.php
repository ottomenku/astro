<?php

namespace App\Http\Controllers;

use App\Services\DailyHoroscopeService;
use App\Support\HoroscopePeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, DailyHoroscopeService $dailyHoroscope): View
    {
        $period = HoroscopePeriod::normalize($request->query('period'));
        $message = null;
        $error = null;

        try {
            $message = $dailyHoroscope->forHomepage($request->user(), null, $period);
        } catch (\Throwable $exception) {
            report($exception);

            if ($exception instanceof \Illuminate\Database\QueryException
                && (str_contains($exception->getMessage(), 'period_type')
                    || str_contains($exception->getMessage(), 'period_start')
                    || str_contains($exception->getMessage(), 'horoscope_daily_messages'))) {
                $error = __('horoscope.schema_outdated');
            } else {
                $error = __('daily.error');
            }
        }

        return view('home', [
            'daily' => $message,
            'error' => $error,
            'period' => $period,
        ]);
    }
}
