<?php

namespace Tests\Feature\Horoscope;

use App\Models\User;
use App\Services\HoroscopeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class HoroscopeApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validCalculatePayload(): array
    {
        return [
            'natal' => [
                'datetime_utc' => '1990-05-15T08:30:00.000000Z',
                'lat' => 47.4979,
                'lon' => 19.0402,
            ],
            'transit' => [
                'datetime_utc' => '2024-06-22T10:00:00.000000Z',
                'lat' => 47.4979,
                'lon' => 19.0402,
            ],
            'sidereal' => false,
            'house_system' => 'placidus',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fakeChartResponse(): array
    {
        return [
            'sidereal' => false,
            'ayanamsa' => null,
            'house_system' => 'placidus',
            'natal' => [
                'asc' => 120.5,
                'mc' => 30.2,
                'houses' => array_fill(0, 12, 0.0),
                'planets' => [
                    ['name' => 'Sun', 'longitude' => 54.3, 'sign' => 'Taurus', 'sign_degree' => 24.3, 'house' => 1],
                ],
                'aspects' => [],
            ],
            'transit' => [
                'asc' => 200.1,
                'mc' => 110.0,
                'houses' => array_fill(0, 12, 0.0),
                'planets' => [],
                'aspects' => [],
            ],
        ];
    }

    public function test_guest_cannot_access_horoscope_page(): void
    {
        $this->get(route('horoscope.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_horoscope_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('horoscope.index'))
            ->assertOk();
    }

    public function test_guest_cannot_calculate_horoscope(): void
    {
        $this->postJson(route('horoscope.calculate'), $this->validCalculatePayload())
            ->assertUnauthorized();
    }

    public function test_calculate_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('horoscope.calculate'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['natal', 'transit']);
    }

    public function test_calculate_returns_chart_json_when_calculator_succeeds(): void
    {
        $user = User::factory()->create();
        $fake = $this->fakeChartResponse();

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($fake) {
            $mock->shouldReceive('calculate')
                ->once()
                ->andReturn($fake);
        });

        $response = $this->actingAs($user)
            ->postJson(route('horoscope.calculate'), $this->validCalculatePayload());

        $response->assertOk()
            ->assertJsonPath('house_system', 'placidus')
            ->assertJsonPath('natal.asc', 120.5)
            ->assertJsonStructure(['natal' => ['planets', 'houses', 'asc'], 'transit']);
    }

    public function test_calculate_returns_error_when_calculator_fails(): void
    {
        $user = User::factory()->create();

        $this->mock(HoroscopeCalculator::class, function ($mock) {
            $mock->shouldReceive('calculate')
                ->once()
                ->andThrow(new \RuntimeException('python error'));
        });

        $this->actingAs($user)
            ->postJson(route('horoscope.calculate'), $this->validCalculatePayload())
            ->assertStatus(500)
            ->assertJsonPath('error', 'A horoszkóp számítás sikertelen.');
    }

    public function test_geocode_returns_empty_results_for_short_query(): void
    {
        $user = User::factory()->create();

        Http::fake();

        $this->actingAs($user)
            ->getJson(route('horoscope.geocode', ['q' => 'ab']))
            ->assertOk()
            ->assertJson(['results' => []]);

        Http::assertNothingSent();
    }

    public function test_geocode_returns_results_from_nominatim(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                [
                    'display_name' => 'Budapest, Hungary',
                    'lat' => '47.4979',
                    'lon' => '19.0402',
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->getJson(route('horoscope.geocode', ['q' => 'Budapest']))
            ->assertOk()
            ->assertJsonPath('results.0.display_name', 'Budapest, Hungary')
            ->assertJsonPath('results.0.lat', '47.4979');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
