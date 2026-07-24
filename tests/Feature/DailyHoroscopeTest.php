<?php

namespace Tests\Feature;

use App\Models\DailyHoroscopeMessage;
use App\Models\DailyHoroscopeSetting;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Models\UserDailyHoroscopeSetting;
use App\Services\DailyHoroscopePromptBuilder;
use App\Services\DailyHoroscopeService;
use App\Services\HoroscopeCalculator;
use App\Support\HoroscopePeriod;
use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class DailyHoroscopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AstrologyScoringProfileSeeder::class);
    }

    public function test_home_page_shows_published_daily_message(): void
    {
        DailyHoroscopeMessage::create($this->publishedMessageAttributes([
            'motto' => 'Ma bízz a ritmusban.',
            'summary' => 'A csillagok ma támogató hangulatot sugallnak.',
        ]));

        $this->withSession(['locale' => 'hu'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Ma bízz a ritmusban.')
            ->assertSee('Mit üzennek a csillagok mára');
    }

    public function test_home_page_hides_unpublished_draft(): void
    {
        DailyHoroscopeMessage::create($this->publishedMessageAttributes([
            'status' => DailyHoroscopeMessage::STATUS_DRAFT,
            'motto' => 'Titkos piszkozat.',
            'summary' => 'Rejtett.',
            'health' => 'Rejtett.',
            'money' => 'Rejtett.',
            'relationships' => 'Rejtett.',
            'work' => 'Rejtett.',
            'published_at' => null,
        ]));

        DailyHoroscopeSetting::forLocale('hu')->update(['auto_publish' => false]);

        $this->withSession(['locale' => 'hu'])
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Titkos piszkozat.')
            ->assertSee(__('daily.unpublished'));
    }

    public function test_admin_can_publish_draft(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        DailyHoroscopeMessage::create($this->publishedMessageAttributes([
            'status' => DailyHoroscopeMessage::STATUS_DRAFT,
            'motto' => 'Piszkozat mottó.',
            'summary' => 'Piszkozat összefoglaló.',
            'health' => 'Egészség szöveg.',
            'money' => 'Pénz szöveg.',
            'relationships' => 'Párkapcsolat szöveg.',
            'work' => 'Munka szöveg.',
            'published_at' => null,
        ]));

        $this->actingAs($admin)
            ->post(route('admin.daily-horoscope.publish'), ['locale' => 'hu'])
            ->assertRedirect();

        $this->assertDatabaseHas('daily_horoscope_messages', [
            'motto' => 'Piszkozat mottó.',
            'status' => DailyHoroscopeMessage::STATUS_PUBLISHED,
        ]);
    }

    public function test_daily_message_uses_admin_selected_scoring_profile(): void
    {
        $profiles = ScoringProfile::query()->orderBy('id')->get();
        $integrated = $profiles->first(fn (ScoringProfile $p) => $p->isIntegrated());
        $this->assertNotNull($integrated);

        DailyHoroscopeSetting::forLocale('hu')->update([
            'scoring_profile_id' => $integrated->id,
            'auto_publish' => true,
        ]);

        $mockChart = $this->mockChartPayload();

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Nap az Oroszlánban – ragyogj.',
                            'summary' => 'A Nap az 5. házban erős.',
                            'health' => 'Hold és Mars figyelendő.',
                            'money' => 'Vénusz a 2. házban stabil.',
                            'relationships' => 'Mars-Vénusz szög aktív.',
                            'work' => 'MC a Mérlegben egyensúlyt kér.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 10],
            ]),
        ]);

        $message = app(DailyHoroscopeService::class)->publishedForToday('hu');

        $this->assertSame('Integrált pontozás', $message?->scoring_profile_name);
        $this->assertTrue($message?->isPublished());
    }

    public function test_home_page_triggers_global_generation_for_guest_on_first_visit(): void
    {
        $mockChart = $this->mockChartPayload();

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Első látogató napja.',
                            'summary' => 'A mai üzenet automatikusan készült.',
                            'health' => 'Egészség szöveg.',
                            'money' => 'Pénz szöveg.',
                            'relationships' => 'Párkapcsolat szöveg.',
                            'work' => 'Munka szöveg.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 10],
            ]),
        ]);

        $this->withSession(['locale' => 'hu'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Első látogató napja.');

        $this->assertDatabaseCount('daily_horoscope_messages', 1);
        $this->assertDatabaseHas('daily_horoscope_messages', [
            'motto' => 'Első látogató napja.',
            'status' => DailyHoroscopeMessage::STATUS_PUBLISHED,
        ]);
    }

    public function test_home_page_generates_global_even_when_user_has_personal_daily(): void
    {
        $user = User::factory()->create();
        UserDailyHoroscopeSetting::forUser($user)->update(['use_personal_daily' => true]);

        $mockChart = $this->mockChartPayload();

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Globális mottó.',
                            'summary' => 'Globális összefoglaló.',
                            'health' => 'Egészség.',
                            'money' => 'Pénz.',
                            'relationships' => 'Kapcsolat.',
                            'work' => 'Munka.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 10],
            ], 200),
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->get(route('home'))
            ->assertOk();

        $this->assertDatabaseHas('daily_horoscope_messages', [
            'motto' => 'Globális mottó.',
            'status' => DailyHoroscopeMessage::STATUS_PUBLISHED,
        ]);
        $this->assertDatabaseHas('user_daily_horoscope_messages', [
            'user_id' => $user->id,
            'motto' => 'Globális mottó.',
        ]);
    }

    public function test_daily_message_is_generated_once_per_locale_and_date(): void
    {
        $user = User::factory()->create();
        DailyHoroscopeSetting::forLocale('hu')->update(['auto_publish' => true]);

        $mockChart = $this->mockChartPayload();

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'motto' => 'Ma merész légy.',
                            'summary' => 'Energikus nap vár.',
                            'health' => 'Mozgás jólesik.',
                            'money' => 'Várj a döntéssel.',
                            'relationships' => 'Meleg hangok.',
                            'work' => 'Kreatív munka előny.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 10],
            ]),
        ]);

        $service = app(DailyHoroscopeService::class);
        $first = $service->forToday($user, 'hu');
        $second = $service->forToday(User::factory()->create(), 'hu');

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('daily_horoscope_messages', 1);
        $this->assertSame('Ma merész légy.', $first->motto);
    }

    public function test_home_page_shows_published_weekly_message(): void
    {
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::WEEKLY);

        DailyHoroscopeMessage::create($this->publishedMessageAttributes([
            'period_type' => HoroscopePeriod::WEEKLY,
            'period_start' => $bounds['start']->toDateString(),
            'period_end' => $bounds['end']->toDateString(),
            'forecast_date' => $bounds['forecast_date']->toDateString(),
            'motto' => 'Erős hét indul.',
            'summary' => 'A hét eleje és vége között markáns váltás látszik.',
        ]));

        $this->withSession(['locale' => 'hu'])
            ->get(route('home', ['period' => HoroscopePeriod::WEEKLY]))
            ->assertOk()
            ->assertSee('Erős hét indul.')
            ->assertSee(__('daily.period_weekly'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function publishedMessageAttributes(array $overrides = []): array
    {
        $date = now('Europe/Budapest')->toDateString();

        return array_merge([
            'forecast_date' => $date,
            'locale' => 'hu',
            'period_type' => HoroscopePeriod::DAILY,
            'period_start' => $date,
            'period_end' => $date,
            'status' => DailyHoroscopeMessage::STATUS_PUBLISHED,
            'chart_datetime_utc' => now(),
            'chart_payload' => ['natal' => []],
            'period_context' => ['period_type' => HoroscopePeriod::DAILY],
            'score_payload' => ['rating_label' => 'erős'],
            'scoring_profile_name' => 'Astro Motto alap pontozás',
            'motto' => 'Mottó.',
            'summary' => 'Összefoglaló.',
            'health' => 'Egészség.',
            'money' => 'Pénz.',
            'relationships' => 'Kapcsolat.',
            'work' => 'Munka.',
            'generated_at' => now(),
            'published_at' => now(),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function mockChartPayload(): array
    {
        return [
            'sidereal' => false,
            'house_system' => 'placidus',
            'natal' => [
                'datetime_utc' => now()->toIso8601String(),
                'lat' => 47.5,
                'lon' => 19.04,
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
                ],
                'aspects' => [],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
