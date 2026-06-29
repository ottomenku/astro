<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileHoroscopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_horoscope_settings_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.horoscope.edit'))
            ->assertOk()
            ->assertSee(__('app.profile_horoscope'))
            ->assertSee(__('app.horoscope_type'));
    }

    public function test_horoscope_settings_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.horoscope.update'), [
            'house_system' => 'whole_sign',
            'zodiac_mode' => 'sidereal',
            'current_place_label' => 'Debrecen',
            'current_lat' => 47.5316,
            'current_lon' => 21.6273,
            'current_tz_offset' => 2,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.horoscope.edit'));

        $user->refresh();

        $this->assertSame('whole_sign', $user->house_system);
        $this->assertSame('sidereal', $user->zodiac_mode);
        $this->assertSame('Debrecen', $user->current_place_label);
        $this->assertEqualsWithDelta(47.5316, (float) $user->current_lat, 0.0001);
        $this->assertEqualsWithDelta(21.6273, (float) $user->current_lon, 0.0001);
    }

    public function test_house_system_and_zodiac_mode_are_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.horoscope.edit'))
            ->patch(route('profile.horoscope.update'), [
                'current_place_label' => 'Budapest',
            ])
            ->assertSessionHasErrors(['house_system', 'zodiac_mode']);
    }
}
