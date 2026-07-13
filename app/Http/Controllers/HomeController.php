<?php

namespace App\Http\Controllers;

use App\Services\DailyHoroscopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, DailyHoroscopeService $dailyHoroscope): View
    {
        $message = null;
        $error = null;

        try {
            $message = $dailyHoroscope->forHomepage($request->user());
        } catch (\Throwable $exception) {
            report($exception);
            $error = __('daily.error');
        }

        return view('home', [
            'daily' => $message,
            'error' => $error,
        ]);
    }
}
