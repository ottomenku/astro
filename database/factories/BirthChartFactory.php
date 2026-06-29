<?php

namespace Database\Factories;

use App\Models\BirthChart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<BirthChart>
 */
class BirthChartFactory extends Factory
{
    protected $model = BirthChart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $offset = 2.0;
        $local = Carbon::parse('1990-05-15 14:30', 'UTC');

        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_datetime_utc' => $local->copy()->subHours($offset),
            'birth_tz_offset' => $offset,
            'birth_place_label' => 'Budapest, Hungary',
            'birth_lat' => 47.4979,
            'birth_lon' => 19.0402,
            'time_accuracy' => 3,
            'corrected_datetime_utc' => null,
            'is_default' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
