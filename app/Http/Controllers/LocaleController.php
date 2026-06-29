<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'hu'];

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', self::SUPPORTED)],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);

        return redirect()
            ->back()
            ->withCookie(cookie()->forever('locale', $locale));
    }
}
