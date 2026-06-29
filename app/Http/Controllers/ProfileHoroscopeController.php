<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileHoroscopeUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileHoroscopeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.horoscope', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileHoroscopeUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return Redirect::route('profile.horoscope.edit')->with('status', 'horoscope-updated');
    }
}
