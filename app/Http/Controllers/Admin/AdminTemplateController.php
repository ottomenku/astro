<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteUiSetting;
use App\Support\UiTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTemplateController extends Controller
{
    public function index(): View
    {
        $setting = SiteUiSetting::current();

        return view('admin.templates.index', [
            'activeTemplate' => $setting->active_template,
            'templates' => UiTemplateCatalog::optionsForAdmin(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'template' => [
                'required',
                'string',
                Rule::in(array_keys(UiTemplateCatalog::all())),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! UiTemplateCatalog::isAvailable((string) $value)) {
                        $fail(__('app.template_not_available'));
                    }
                },
            ],
        ]);

        SiteUiSetting::current()->activate($request->string('template')->toString());

        return redirect()
            ->route('admin.templates.index')
            ->with('status', __('app.template_switched'));
    }
}
