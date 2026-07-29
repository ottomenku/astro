<?php

namespace Tests\Feature\Horoscope;

use App\Models\BirthChart;
use App\Models\DailyHoroscopeSetting;
use App\Models\User;
use App\Services\HoroscopeCalculator;
use App\Support\HoroscopeLlmConfig;
use App\Support\HoroscopeTopicCatalog;
use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HoroscopeGenerationOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AstrologyScoringProfileSeeder::class);
    }

    public function test_daily_message_accepts_user_focus_and_detail_level(): void
    {
        $user = User::factory()->create(['zodiac_mode' => 'tropical', 'house_system' => 'placidus']);
        $chart = BirthChart::factory()->for($user)->create([
            'birth_datetime_utc' => '1990-05-10T10:00:00Z',
            'birth_lat' => 47.5,
            'birth_lon' => 19.0,
            'birth_tz_offset' => 2,
        ]);

        DailyHoroscopeSetting::forLocale('hu')->update([
            'message_sentences_short' => 20,
            'message_sentences_normal' => 50,
            'message_sentences_detailed' => 100,
        ]);

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
                            'motto' => 'Ma nyitott vagy.',
                            'summary' => 'A Nap ma támogat.',
                            'health' => 'Pihenj eleget.',
                            'money' => 'Stabil nap.',
                            'relationships' => 'Nyílt szó segít.',
                            'work' => 'Fókuszálj.',
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('horoscope.daily-message'), [
                'mode' => 'single',
                'birth_chart_id' => $chart->id,
                'period' => 'daily',
                'user_focus' => 'Munka és pénz',
                'detail_level' => 'short',
            ]);

        $response->assertOk()
            ->assertJsonPath('motto', 'Ma nyitott vagy.');
    }

    public function test_admin_can_view_and_update_horoscope_prompt(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->getJson(route('admin.horoscope-prompts.show', [
                'context' => HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE,
                'locale' => 'hu',
            ]))
            ->assertOk()
            ->assertJsonPath('context', HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE)
            ->assertJsonStructure([
                'system_prompt',
                'user_prompt',
                'instructions_prompt',
                'default_instructions_prompt',
            ]);

        $this->actingAs($admin)
            ->putJson(route('admin.horoscope-prompts.update'), [
                'context' => HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE,
                'locale' => 'hu',
                'prompt' => 'Egyedi személyes üzenet prompt.',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(
            'Egyedi személyes üzenet prompt.',
            app(HoroscopeLlmConfig::class)->prompt(HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE, 'hu'),
        );
    }

    public function test_admin_prompt_preview_includes_system_and_user_prompts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.horoscope-prompts.show', [
                'context' => HoroscopeLlmConfig::PROMPT_PERSONAL_EXPLANATION,
                'locale' => 'hu',
            ]));

        $response->assertOk();
        $this->assertNotSame('', trim((string) $response->json('system_prompt')));
        $this->assertStringContainsString('explanation', strtolower((string) $response->json('system_prompt')));
    }

    public function test_non_admin_cannot_update_horoscope_prompt(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->putJson(route('admin.horoscope-prompts.update'), [
                'context' => HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE,
                'locale' => 'hu',
                'prompt' => 'Tiltott.',
            ])
            ->assertForbidden();
    }
}
