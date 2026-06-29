<?php

namespace App\Http\Controllers;

use App\Http\Requests\BirthChartRequest;
use App\Models\BirthChart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileBirthChartController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.birth-charts.index', [
            'birthCharts' => $request->user()->birthCharts()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('profile.birth-charts.create', [
            'birthChart' => new BirthChart([
                'birth_tz_offset' => 2,
                'time_accuracy' => 3,
                'is_default' => false,
            ]),
        ]);
    }

    public function store(BirthChartRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->mapRequestToAttributes($request);

        if ($user->birthCharts()->doesntExist()) {
            $data['is_default'] = true;
        } elseif ($request->boolean('is_default')) {
            $this->clearDefaultFlag($user->id);
            $data['is_default'] = true;
        }

        $user->birthCharts()->create($data);

        return Redirect::route('profile.birth-charts.index')->with('status', 'birth-chart-created');
    }

    public function edit(Request $request, BirthChart $birthChart): View
    {
        $this->authorizeChart($request, $birthChart);

        return view('profile.birth-charts.edit', [
            'birthChart' => $birthChart,
        ]);
    }

    public function update(BirthChartRequest $request, BirthChart $birthChart): RedirectResponse
    {
        $this->authorizeChart($request, $birthChart);

        $data = $this->mapRequestToAttributes($request);

        if ($request->boolean('is_default')) {
            $this->clearDefaultFlag($birthChart->user_id, $birthChart->id);
            $data['is_default'] = true;
        } else {
            $data['is_default'] = $birthChart->is_default;
        }

        $birthChart->update($data);

        return Redirect::route('profile.birth-charts.index')->with('status', 'birth-chart-updated');
    }

    public function destroy(Request $request, BirthChart $birthChart): RedirectResponse
    {
        $this->authorizeChart($request, $birthChart);

        $wasDefault = $birthChart->is_default;
        $userId = $birthChart->user_id;
        $birthChart->delete();

        if ($wasDefault) {
            $next = BirthChart::query()->where('user_id', $userId)->latest()->first();
            $next?->update(['is_default' => true]);
        }

        return Redirect::route('profile.birth-charts.index')->with('status', 'birth-chart-deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRequestToAttributes(BirthChartRequest $request): array
    {
        $offset = (float) $request->input('birth_tz_offset');
        $birthUtc = BirthChart::localPartsToUtc(
            $request->string('birth_date')->toString(),
            $request->string('birth_time')->toString(),
            $offset,
        );

        $correctedUtc = null;
        if ($request->filled('corrected_date') && $request->filled('corrected_time')) {
            $correctedUtc = BirthChart::localPartsToUtc(
                $request->string('corrected_date')->toString(),
                $request->string('corrected_time')->toString(),
                $offset,
            );
        }

        return [
            'name' => $request->string('name')->toString(),
            'gender' => $request->string('gender')->toString(),
            'birth_datetime_utc' => $birthUtc,
            'birth_tz_offset' => $offset,
            'birth_place_label' => $request->input('birth_place_label'),
            'birth_lat' => $request->input('birth_lat'),
            'birth_lon' => $request->input('birth_lon'),
            'time_accuracy' => (int) $request->input('time_accuracy'),
            'corrected_datetime_utc' => $correctedUtc,
        ];
    }

    private function clearDefaultFlag(int $userId, ?int $exceptId = null): void
    {
        $query = BirthChart::query()->where('user_id', $userId);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_default' => false]);
    }

    private function authorizeChart(Request $request, BirthChart $birthChart): void
    {
        abort_if($birthChart->user_id !== $request->user()->id, 403);
    }
}
