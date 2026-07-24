<?php

namespace Tests\Feature\Admin;

use App\Models\AstrologyEntry;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdminAstrologyEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_astrology_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.astrology-entries.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_astrology_entry_list_and_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $firstClicker = User::factory()->create([
            'name' => 'Első Kattintó',
            'email' => 'elso@example.com',
        ]);

        $entry = AstrologyEntry::create([
            'type' => 'planet',
            'key' => 'Sun',
            'locale' => 'hu',
            'title' => 'Nap',
            'question' => 'Mit jelent a Nap?',
            'answer' => 'A Nap a tudat jelképe.',
            'created_by_user_id' => $firstClicker->id,
            'click_count' => 3,
            'first_clicked_by_user_id' => $firstClicker->id,
            'first_clicked_at' => now()->subDay(),
            'last_clicked_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.astrology-entries.index'))
            ->assertOk()
            ->assertSee('Nap')
            ->assertSee('Sun')
            ->assertSee('Első Kattintó')
            ->assertSee('3');

        $this->actingAs($admin)
            ->get(route('admin.astrology-entries.show', $entry))
            ->assertOk()
            ->assertSee('Mit jelent a Nap?')
            ->assertSee('A Nap a tudat jelképe.')
            ->assertSee('elso@example.com');
    }

    public function test_element_click_increments_count_and_records_first_clicker(): void
    {
        $creator = User::factory()->create();
        $secondUser = User::factory()->create(['name' => 'Második']);

        AstrologyEntry::create([
            'type' => 'sign',
            'key' => 'Aries',
            'locale' => 'hu',
            'title' => 'Kos',
            'question' => 'test',
            'answer' => 'Kos válasz.',
            'created_by_user_id' => $creator->id,
            'click_count' => 1,
            'first_clicked_by_user_id' => $creator->id,
            'first_clicked_at' => now()->subHour(),
            'last_clicked_at' => now()->subHour(),
        ]);

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldNotReceive('sendWithSystem');
        });

        $this->actingAs($secondUser)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.info'), [
                'type' => 'sign',
                'key' => 'Aries',
                'title' => 'Kos',
            ])
            ->assertOk()
            ->assertJsonPath('cached', true);

        $this->assertDatabaseHas('astrology_entries', [
            'type' => 'sign',
            'key' => 'Aries',
            'click_count' => 2,
            'first_clicked_by_user_id' => $creator->id,
        ]);
    }

    public function test_new_element_click_sets_initial_click_metadata(): void
    {
        $user = User::factory()->create();

        $this->mock(ChatService::class, function ($mock) {
            $mock->shouldReceive('sendWithSystem')
                ->once()
                ->andReturn([
                    'answer' => 'A Hold az érzelmek jelképe.',
                    'usage' => ['total_tokens' => 30],
                ]);
        });

        $this->actingAs($user)
            ->withSession(['locale' => 'hu'])
            ->postJson(route('horoscope.info'), [
                'type' => 'planet',
                'key' => 'Moon',
                'title' => 'Hold',
            ])
            ->assertOk()
            ->assertJsonPath('cached', false);

        $this->assertDatabaseHas('astrology_entries', [
            'type' => 'planet',
            'key' => 'Moon',
            'click_count' => 1,
            'first_clicked_by_user_id' => $user->id,
            'created_by_user_id' => $user->id,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
