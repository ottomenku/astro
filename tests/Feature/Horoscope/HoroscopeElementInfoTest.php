<?php

namespace Tests\Feature\Horoscope;

use App\Models\AstrologyEntry;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\TestCase;

class HoroscopeElementInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_fetch_element_info(): void
    {
        $this->postJson(route('horoscope.info'), [
            'type' => 'sign',
            'key' => 'Aries',
            'title' => 'Kos',
        ])->assertUnauthorized();
    }

    public function test_element_info_returns_cached_entry_without_calling_llm(): void
    {
        $user = User::factory()->create();

        AstrologyEntry::create([
            'type' => 'sign',
            'key' => 'Aries',
            'locale' => 'hu',
            'title' => 'Kos',
            'question' => 'Mit jelent az asztrológiában a Kos jegy?',
            'answer' => 'A Kos pozitív, tüzes, kardinális jegy.',
            'created_by_user_id' => $user->id,
        ]);

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldNotReceive('sendWithSystem');
        });

        $this->postElementInfo($user, [
            'type' => 'sign',
            'key' => 'Aries',
            'title' => 'Kos',
        ])
            ->assertOk()
            ->assertJsonPath('title', 'Kos')
            ->assertJsonPath('answer', 'A Kos pozitív, tüzes, kardinális jegy.')
            ->assertJsonPath('cached', true);
    }

    public function test_element_info_generates_and_persists_when_missing(): void
    {
        $user = User::factory()->create();

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldReceive('sendWithSystem')
                ->once()
                ->andReturn([
                    'answer' => 'A Nap a tudat, az életenergia és az ego jelképe a horoszkópban.',
                    'usage' => ['total_tokens' => 42],
                ]);
        });

        $this->postElementInfo($user, [
            'type' => 'planet',
            'key' => 'Sun',
            'title' => 'Nap',
        ])
            ->assertOk()
            ->assertJsonPath('cached', false)
            ->assertJsonPath('title', 'Nap');

        $this->assertDatabaseHas('astrology_entries', [
            'type' => 'planet',
            'key' => 'Sun',
            'locale' => 'hu',
            'title' => 'Nap',
        ]);
    }

    public function test_element_info_rejects_unknown_key(): void
    {
        $user = User::factory()->create();

        $this->postElementInfo($user, [
            'type' => 'planet',
            'key' => 'NotAPlanet',
            'title' => 'X',
        ])
            ->assertStatus(422);
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function postElementInfo(User $user, array $payload): TestResponse
    {
        return $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.info'), $payload);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
