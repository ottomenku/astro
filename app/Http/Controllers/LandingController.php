<?php

namespace App\Http\Controllers;

use App\Support\SiteMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (SiteMode::isExpert()) {
            return redirect()->route('horoscope.index');
        }

        if (auth()->check()) {
            return redirect()->route('message.index');
        }

        return view('landing');
    }
}
