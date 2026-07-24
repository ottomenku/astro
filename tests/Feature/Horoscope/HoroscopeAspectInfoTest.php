<?php

namespace Tests\Feature\Horoscope;

use App\Models\AstrologyEntry;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HoroscopeAspectInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_fetch_aspect_info(): void
    {
        $this->postJson(route('horoscope.aspect-info'), [
            'mode' => 'natal',
            'aspect' => 'trine',
            'body1' => ['name' => 'Sun', 'sign' => 'Leo', 'house' => 5, 'gender' => 'male'],
            'body2' => ['name' => 'Moon', 'sign' => 'Aries', 'house' => 1, 'gender' => 'male'],
        ])->assertUnauthorized();
    }

    public function test_aspect_info_returns_cached_entry_without_calling_llm(): void
    {
        $user = User::factory()->create();

        AstrologyEntry::create([
            'type' => 'aspect',
            'key' => 'natal|Sun|Moon|trine',
            'locale' => 'hu',
            'title' => 'Nap trigon Hold',
            'question' => 'test',
            'answer' => 'Harmonikus belső egyensúly.',
            'created_by_user_id' => $user->id,
        ]);

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldNotReceive('sendWithSystem');
        });

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.aspect-info'), [
                'mode' => 'natal',
                'aspect' => 'trine',
                'body1' => ['name' => 'Sun', 'sign' => 'Leo', 'house' => 5, 'gender' => 'male'],
                'body2' => ['name' => 'Moon', 'sign' => 'Aries', 'house' => 1, 'gender' => 'male'],
            ])
            ->assertOk()
            ->assertJsonPath('cached', true)
            ->assertJsonPath('answer', 'Harmonikus belső egyensúly.');
    }

    public function test_aspect_info_generates_synastry_interpretation_when_missing(): void
    {
        $user = User::factory()->create();

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldReceive('sendWithSystem')
                ->once()
                ->andReturn([
                    'answer' => 'A kapcsolatban erős vonzalom és néha makacsság is megjelenhet.',
                    'usage' => ['total_tokens' => 55],
                ]);
        });

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.aspect-info'), [
                'mode' => 'synastry',
                'aspect' => 'square',
                'body1' => ['name' => 'Venus', 'sign' => 'Taurus', 'house' => 2, 'gender' => 'female'],
                'body2' => ['name' => 'Mars', 'sign' => 'Leo', 'house' => 5, 'gender' => 'male'],
                'meta' => [
                    'chart_a_id' => 1,
                    'chart_b_id' => 2,
                    'side_a_is_now' => false,
                    'side_b_is_now' => false,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('cached', false);

        $this->assertDatabaseHas('astrology_entries', [
            'type' => 'aspect',
            'key' => 'synastry|Venus|Mars|square|1|2',
            'locale' => 'hu',
        ]);
    }

    public function test_aspect_info_includes_retrograde_in_cached_flow(): void
    {
        $user = User::factory()->create();

        AstrologyEntry::create([
            'type' => 'aspect',
            'key' => 'natal|Mercury|Venus|trine',
            'locale' => 'hu',
            'title' => 'Merkúr trigon Vénusz',
            'question' => 'test',
            'answer' => 'Belső finomhangolás.',
            'created_by_user_id' => $user->id,
        ]);

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldNotReceive('sendWithSystem');
        });

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.aspect-info'), [
                'mode' => 'natal',
                'aspect' => 'trine',
                'body1' => ['name' => 'Mercury', 'sign' => 'Virgo', 'house' => 6, 'gender' => 'female', 'retrograde' => true],
                'body2' => ['name' => 'Venus', 'sign' => 'Cancer', 'house' => 4, 'gender' => 'female', 'retrograde' => false],
            ])
            ->assertOk();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
