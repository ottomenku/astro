<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\ChartDisplaySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartDisplaySettingsTest extends TestCase
{
    use RefreshDatabase;
    public function test_defaults_use_placidus_friendly_aspect_colors(): void
    {
        $defaults = ChartDisplaySettings::defaults();

        $this->assertTrue($defaults['aspects']['conjunction']['enabled']);
        $this->assertSame('gray', $defaults['aspects']['conjunction']['color']);
        $this->assertTrue($defaults['objects']['ASC']['enabled']);
        $this->assertSame('gold', $defaults['objects']['Sun']['color']);
    }

    public function test_from_request_updates_only_submitted_entries(): void
    {
        $result = ChartDisplaySettings::fromRequest([
            'aspects' => [
                'square' => ['enabled' => '1', 'color' => 'crimson'],
            ],
            'objects' => [
                'Mars' => ['enabled' => '0', 'color' => 'red'],
            ],
        ]);

        $this->assertSame('crimson', $result['aspects']['square']['color']);
        $this->assertFalse($result['objects']['Mars']['enabled']);
        $this->assertTrue($result['aspects']['trine']['enabled']);
        $this->assertTrue($result['objects']['Sun']['enabled']);
    }

    public function test_rejects_invalid_named_color_in_merge(): void
    {
        $merged = ChartDisplaySettings::merge(ChartDisplaySettings::defaults(), [
            'aspects' => [
                'trine' => ['color' => '#00ff00'],
            ],
        ]);

        $this->assertSame('green', $merged['aspects']['trine']['color']);
    }

    public function test_resolve_uses_admin_defaults_for_users_without_personal_settings(): void
    {
        ChartDisplaySettings::persistAdminDefaults([
            'aspects' => [
                'square' => ['enabled' => '1', 'color' => 'crimson'],
            ],
            'objects' => [],
        ]);

        $user = User::factory()->create();
        $resolved = ChartDisplaySettings::resolve($user);

        $this->assertSame('crimson', $resolved['aspects']['square']['color']);
    }
}
