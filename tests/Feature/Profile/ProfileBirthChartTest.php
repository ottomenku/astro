<?php

namespace Tests\Feature\Profile;

use App\Models\BirthChart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileBirthChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_birth_charts_index_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.birth-charts.index'))
            ->assertOk()
            ->assertSee(__('app.profile_birth_charts'));
    }

    public function test_user_can_create_birth_chart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.birth-charts.store'), [
            'name' => 'Teszt Elek',
            'gender' => 'male',
            'birth_date' => '1990-05-15',
            'birth_time' => '14:30',
            'birth_tz_offset' => 2,
            'birth_place_label' => 'Budapest',
            'birth_lat' => 47.4979,
            'birth_lon' => 19.0402,
            'time_accuracy' => 4,
            'corrected_date' => '1990-05-15',
            'corrected_time' => '14:45',
            'is_default' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.birth-charts.index'));

        $this->assertDatabaseHas('birth_charts', [
            'user_id' => $user->id,
            'name' => 'Teszt Elek',
            'gender' => 'male',
            'time_accuracy' => 4,
            'is_default' => true,
        ]);

        $chart = BirthChart::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($chart);
        $this->assertSame('1990-05-15', $chart->localBirthParts()['date']);
        $this->assertSame('14:30', $chart->localBirthParts()['time']);
        $this->assertSame('14:45', $chart->localCorrectedParts()['time']);
    }

    public function test_first_birth_chart_becomes_default_automatically(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.birth-charts.store'), [
            'name' => 'Első',
            'gender' => 'female',
            'birth_date' => '1985-01-01',
            'birth_time' => '08:00',
            'birth_tz_offset' => 1,
            'time_accuracy' => 3,
        ])->assertSessionHasNoErrors();

        $this->assertTrue(BirthChart::query()->where('user_id', $user->id)->value('is_default'));
    }

    public function test_user_cannot_edit_another_users_birth_chart(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $chart = BirthChart::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('profile.birth-charts.edit', $chart))
            ->assertForbidden();
    }

    public function test_user_can_delete_own_birth_chart(): void
    {
        $user = User::factory()->create();
        $chart = BirthChart::factory()->for($user)->create(['is_default' => true]);

        $this->actingAs($user)
            ->delete(route('profile.birth-charts.destroy', $chart))
            ->assertRedirect(route('profile.birth-charts.index'));

        $this->assertDatabaseMissing('birth_charts', ['id' => $chart->id]);
    }
}
