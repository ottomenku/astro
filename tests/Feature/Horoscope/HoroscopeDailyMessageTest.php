<?php

namespace Tests\Feature\Horoscope;

use App\Models\BirthChart;
use App\Models\HoroscopeChartExplanation;
use App\Models\HoroscopeDailyMessage;
use App\Models\User;
use App\Services\HoroscopeCalculator;
use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class HoroscopeDailyMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AstrologyScoringProfileSeeder::class);
    }

    public function test_guest_cannot_fetch_horoscope_daily_message(): void
    {
        $this->postJson(route('horoscope.daily-message'), [
            'mode' => 'single',
            'birth_chart_id' => 1,
        ])->assertUnauthorized();
    }

    public function test_single_daily_requires_birth_chart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('horoscope.daily-message'), [
                'mode' => 'single',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', __('horoscope.daily_select_birth_chart'));
    }

    public function test_single_daily_generates_personal_message_for_birth_chart(): void
    {
        $user = User::factory()->create(['zodiac_mode' => 'tropical', 'house_system' => 'placidus']);
        $chart = BirthChart::factory()->for($user)->create([
            'name' => 'Teszt Elek',
            'birth_datetime_utc' => '1990-05-10T10:00:00Z',
            'birth_lat' => 47.5,
            'birth_lon' => 19.0,
            'birth_tz_offset' => 2,
        ]);

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')->andReturn([
                'natal' => [
                    'datetime_utc' => now()->toIso8601String(),
                    'planets' => [
                        ['name' => 'Sun', 'sign' => 'Taurus', 'sign_degree' => 20, 'longitude' => 50],
                        ['name' => 'Moon', 'sign' => 'Cancer', 'sign_degree' => 10, 'longitude' => 100],
                    ],
                    'fixed_stars' => [],
                ],
            ]);
        });

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Ma nyitott vagy.',
                            'summary' => 'A Nap és a Hold ma támogat.',
                            'health' => 'Pihenj eleget.',
                            'money' => 'Stabil nap.',
                            'relationships' => 'Nyílt szó segít.',
                            'work' => 'Fókuszálj.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 120],
            ], 200),
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.daily-message'), [
                'mode' => 'single',
                'birth_chart_id' => $chart->id,
            ])
            ->assertOk()
            ->assertJsonPath('kind', HoroscopeDailyMessage::KIND_PERSONAL)
            ->assertJsonPath('motto', 'Ma nyitott vagy.');

        $this->assertDatabaseHas('horoscope_daily_messages', [
            'user_id' => $user->id,
            'kind' => HoroscopeDailyMessage::KIND_PERSONAL,
            'birth_chart_id' => $chart->id,
            'cache_key' => 'personal:'.$chart->id,
            'period_type' => 'daily',
        ]);
    }

    public function test_single_weekly_message_is_cached_by_period(): void
    {
        $user = User::factory()->create(['zodiac_mode' => 'tropical', 'house_system' => 'placidus']);
        $chart = BirthChart::factory()->for($user)->create();

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')->andReturn([
                'natal' => [
                    'datetime_utc' => now()->toIso8601String(),
                    'planets' => [
                        ['name' => 'Sun', 'sign' => 'Taurus', 'sign_degree' => 20, 'longitude' => 50],
                    ],
                    'fixed_stars' => [],
                ],
            ]);
        });

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Erős hét.',
                            'summary' => str_repeat('Heti összefoglaló mondat. ', 20),
                            'health' => str_repeat('Egészség mondat. ', 12),
                            'money' => str_repeat('Pénz mondat. ', 12),
                            'relationships' => str_repeat('Kapcsolat mondat. ', 12),
                            'work' => str_repeat('Munka mondat. ', 12),
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 120],
            ], 200),
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.daily-message'), [
                'mode' => 'single',
                'birth_chart_id' => $chart->id,
                'period' => 'weekly',
            ])
            ->assertOk()
            ->assertJsonPath('period', 'weekly')
            ->assertJsonPath('motto', 'Erős hét.');

        $this->assertDatabaseHas('horoscope_daily_messages', [
            'user_id' => $user->id,
            'kind' => HoroscopeDailyMessage::KIND_PERSONAL,
            'period_type' => 'weekly',
            'cache_key' => 'personal:'.$chart->id,
        ]);
    }

    public function test_explanation_is_generated_once_and_cached(): void
    {
        $user = User::factory()->create(['zodiac_mode' => 'tropical', 'house_system' => 'placidus']);
        $chart = BirthChart::factory()->for($user)->create();

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')->andReturn([
                'natal' => [
                    'datetime_utc' => now()->toIso8601String(),
                    'planets' => [
                        ['name' => 'Sun', 'sign' => 'Taurus', 'sign_degree' => 20, 'longitude' => 50],
                    ],
                    'fixed_stars' => [],
                ],
            ]);
        });

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'explanation' => str_repeat('Részletes kifejtés mondat. ', 12),
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 80],
            ], 200),
        ]);

        $payload = [
            'mode' => 'single',
            'birth_chart_id' => $chart->id,
        ];

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.daily-message.explanation'), $payload)
            ->assertOk()
            ->assertJsonStructure(['explanation']);

        $this->actingAs($user)
            ->postJson(route('horoscope.daily-message.explanation'), $payload)
            ->assertOk();

        Http::assertSentCount(1);
        $this->assertDatabaseHas('horoscope_chart_explanations', [
            'user_id' => $user->id,
            'cache_key' => 'profile:personal:'.$chart->id,
        ]);

        $explanation = HoroscopeChartExplanation::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($explanation?->explanation);
    }

    public function test_now_explanation_is_generated_and_cached(): void
    {
        $user = User::factory()->create([
            'zodiac_mode' => 'tropical',
            'house_system' => 'placidus',
            'current_lat' => 47.5,
            'current_lon' => 19.0,
        ]);

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')->andReturn([
                'natal' => [
                    'datetime_utc' => now()->toIso8601String(),
                    'planets' => [
                        ['name' => 'Sun', 'sign' => 'Gemini', 'sign_degree' => 10, 'longitude' => 70],
                    ],
                    'fixed_stars' => [],
                ],
            ]);
        });

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'explanation' => str_repeat('Most pillanat kifejtés. ', 12),
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 80],
            ], 200),
        ]);

        $payload = [
            'mode' => 'single',
            'is_now' => true,
            'chart' => [
                'datetime_utc' => '2026-07-24T12:00:00Z',
                'lat' => 47.5,
                'lon' => 19.0,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.daily-message.explanation'), $payload)
            ->assertOk()
            ->assertJsonPath('chart_meta', fn ($value) => str_contains($value, '2026'));

        $this->actingAs($user)
            ->postJson(route('horoscope.daily-message.explanation'), $payload)
            ->assertOk();

        Http::assertSentCount(1);
        $this->assertDatabaseHas('horoscope_chart_explanations', [
            'user_id' => $user->id,
            'birth_chart_id' => null,
        ]);
    }

    public function test_dual_daily_generates_partnership_message(): void
    {
        $user = User::factory()->create(['zodiac_mode' => 'tropical', 'house_system' => 'placidus']);
        $chartA = BirthChart::factory()->for($user)->create([
            'name' => 'Anna',
            'birth_datetime_utc' => '1990-05-10T10:00:00Z',
            'birth_lat' => 47.5,
            'birth_lon' => 19.0,
            'birth_tz_offset' => 2,
        ]);
        $chartB = BirthChart::factory()->for($user)->create([
            'name' => 'Béla',
            'birth_datetime_utc' => '1992-08-20T08:00:00Z',
            'birth_lat' => 47.5,
            'birth_lon' => 19.0,
            'birth_tz_offset' => 2,
        ]);

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')->andReturn([
                'natal' => [
                    'datetime_utc' => now()->toIso8601String(),
                    'planets' => [
                        ['name' => 'Sun', 'sign' => 'Leo', 'sign_degree' => 5, 'longitude' => 125],
                        ['name' => 'Moon', 'sign' => 'Aries', 'sign_degree' => 12, 'longitude' => 12],
                        ['name' => 'Venus', 'sign' => 'Libra', 'sign_degree' => 18, 'longitude' => 198],
                        ['name' => 'Mars', 'sign' => 'Scorpio', 'sign_degree' => 22, 'longitude' => 232],
                    ],
                    'fixed_stars' => [],
                ],
            ]);
        });

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Ma együtt erősebbek vagytok.',
                            'summary' => 'A kapcsolatotokban ma meleg hangulat uralkodik.',
                            'health' => 'Támogassátok egymást.',
                            'money' => 'Közös döntés előnyös.',
                            'relationships' => 'A Vénusz-Mars szinasztria ma aktív.',
                            'work' => 'Együtt könnyebb a fókusz.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 140],
            ], 200),
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.daily-message'), [
                'mode' => 'dual',
                'birth_chart_id_a' => $chartA->id,
                'birth_chart_id_b' => $chartB->id,
            ])
            ->assertOk()
            ->assertJsonPath('kind', HoroscopeDailyMessage::KIND_PARTNERSHIP)
            ->assertJsonPath('badge', __('daily.horoscope_partnership_badge'));

        $firstId = min($chartA->id, $chartB->id);
        $secondId = max($chartA->id, $chartB->id);

        $this->assertDatabaseHas('horoscope_daily_messages', [
            'user_id' => $user->id,
            'kind' => HoroscopeDailyMessage::KIND_PARTNERSHIP,
            'birth_chart_id_a' => $firstId,
            'birth_chart_id_b' => $secondId,
            'cache_key' => 'partnership:'.$firstId.':'.$secondId,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
