<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ChartDisplaySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChartDisplayController extends Controller
{
    public function edit(): View
    {
        return view('admin.chart-display.edit', [
            'chartDisplay' => ChartDisplaySettings::adminDefaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(ChartDisplaySettings::validationRules());

        ChartDisplaySettings::persistAdminDefaults($request->input('chart_display', []));

        return redirect()
            ->route('admin.chart-display.edit')
            ->with('status', __('horoscope.chart_display_admin_saved'));
    }
}
