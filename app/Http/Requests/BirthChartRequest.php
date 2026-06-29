<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BirthChartRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'birth_date' => ['required', 'date'],
            'birth_time' => ['required', 'date_format:H:i'],
            'birth_tz_offset' => ['required', 'numeric', 'between:-14,14'],
            'birth_place_label' => ['nullable', 'string', 'max:255'],
            'birth_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'birth_lon' => ['nullable', 'numeric', 'between:-180,180'],
            'time_accuracy' => ['required', 'integer', 'between:1,5'],
            'corrected_date' => ['nullable', 'date', 'required_with:corrected_time'],
            'corrected_time' => ['nullable', 'date_format:H:i', 'required_with:corrected_date'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
