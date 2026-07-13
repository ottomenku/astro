<?php

namespace Tests\Feature\Horoscope;

use App\Models\BirthChart;
use App\Models\ChartScore;
use App\Models\NatalChart;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Services\AstrologyChartScoringService;
use App\Services\HoroscopeCalculator;
use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HoroscopeScoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AstrologyScoringProfileSeeder::class);
    }

    public function test_scoring_persists_placements_aspects_and_score_from_calculated_data(): void
    {
        $user = User::factory()->create([
            'house_system' => 'whole_sign',
            'zodiac_mode' => 'tropical',
        ]);

        $birthChart = BirthChart::factory()->for($user)->create([
            'is_default' => true,
        ]);

        $mockChart = [
            'sidereal' => false,
            'ayanamsa' => null,
            'house_system' => 'whole_sign',
            'natal' => [
                'datetime_utc' => $birthChart->birth_datetime_utc->utc()->toIso8601String(),
                'lat' => $birthChart->birth_lat,
                'lon' => $birthChart->birth_lon,
                'asc' => 120.0,
                'mc' => 30.0,
                'houses' => array_map(fn ($i) => ($i - 1) * 30.0, range(1, 12)),
                'planets' => [
                    [
                        'name' => 'Sun',
                        'longitude' => 125.0,
                        'sign' => 'Leo',
                        'sign_degree' => 5.0,
                        'house' => 5,
                    ],
                    [
                        'name' => 'Moon',
                        'longitude' => 15.0,
                        'sign' => 'Aries',
                        'sign_degree' => 15.0,
                        'house' => 1,
                    ],
                ],
                'aspects' => [
                    [
                        'p1' => 'Sun',
                        'p2' => 'Moon',
                        'type' => 'trine',
                        'angle' => 120,
                        'orb' => 2.0,
                    ],
                ],
            ],
        ];

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        $score = app(AstrologyChartScoringService::class)->scoreFromCalculatedData(
            $user,
            $mockChart,
            $birthChart->id,
        );

        $this->assertNotNull($score);
        $this->assertDatabaseHas('natal_charts', [
            'birth_chart_id' => $birthChart->id,
            'user_id' => $user->id,
        ]);

        $natalChart = NatalChart::query()->where('birth_chart_id', $birthChart->id)->first();
        $this->assertNotNull($natalChart);
        $this->assertSame(4, $natalChart->placements()->count());
        $this->assertSame(1, $natalChart->aspects()->count());
        $this->assertSame(2, ChartScore::query()->where('natal_chart_id', $natalChart->id)->count());
        $this->assertInstanceOf(ChartScore::class, $score);
        $this->assertNotEmpty($score->rating_label);
        $this->assertGreaterThan(0, $score->total_score);
    }

    public function test_horoscope_chat_includes_scoring_context_in_system_prompt(): void
    {
        $user = User::factory()->create();
        $birthChart = BirthChart::factory()->for($user)->create();
        $profile = ScoringProfile::defaultProfile();
        $natalChart = NatalChart::create([
            'user_id' => $user->id,
            'birth_chart_id' => $birthChart->id,
            'datetime_utc' => now(),
            'lat' => 47.0,
            'lon' => 19.0,
            'sidereal' => false,
            'house_system' => 'placidus',
        ]);

        ChartScore::create([
            'natal_chart_id' => $natalChart->id,
            'scoring_profile_id' => $profile->id,
            'polarity_positive' => 40,
            'polarity_negative' => 20,
            'polarity_balance' => 20,
            'element_fire' => 30,
            'element_earth' => 10,
            'element_air' => 5,
            'element_water' => 5,
            'modality_cardinal' => 15,
            'modality_fixed' => 20,
            'modality_mutable' => 15,
            'total_score' => 65,
            'rating_label' => 'erős',
            'breakdown' => ['placements' => []],
            'calculated_at' => now(),
        ]);

        $this->mock(\App\Services\ChatService::class, function ($mock) use ($user) {
            $mock->shouldReceive('sendWithSystem')
                ->once()
                ->withArgs(function ($passedUser, $prompt, $system) use ($user) {
                    return $passedUser->is($user)
                        && $prompt === 'Mit jelent a Nap a képletemben?'
                        && str_contains($system, 'Pre-calculated chart evaluation')
                        && str_contains($system, 'erős');
                })
                ->andReturn(['answer' => 'test', 'usage' => [], 'conversation' => null]);
        });

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->postJson(route('horoscope.chat'), [
                'prompt' => 'Mit jelent a Nap a képletemben?',
                'birth_chart_id' => $birthChart->id,
            ])
            ->assertOk();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
