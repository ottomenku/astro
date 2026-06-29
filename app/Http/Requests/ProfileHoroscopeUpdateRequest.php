<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileHoroscopeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'house_system' => ['required', 'string', Rule::in(['whole_sign', 'placidus'])],
            'zodiac_mode' => ['required', 'string', Rule::in(['tropical', 'sidereal'])],
            'current_tz_offset' => ['nullable', 'numeric', 'between:-14,14'],
            'current_place_label' => ['nullable', 'string', 'max:255'],
            'current_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'current_lon' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
