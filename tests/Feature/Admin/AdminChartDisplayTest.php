<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\ChartDisplaySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChartDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_chart_display_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.chart-display.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_view_and_update_chart_display_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.chart-display.edit'))
            ->assertOk()
            ->assertSee(__('horoscope.chart_display_admin_title'));

        $payload = $this->chartDisplayFormPayload([
            'aspects' => [
                'trine' => ['enabled' => true, 'color' => 'teal'],
            ],
            'objects' => [
                'Sun' => ['enabled' => true, 'color' => 'orange'],
            ],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.chart-display.update'), [
                'chart_display' => $payload,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.chart-display.edit'));

        $defaults = ChartDisplaySettings::adminDefaults();
        $this->assertSame('teal', $defaults['aspects']['trine']['color']);
        $this->assertSame('orange', $defaults['objects']['Sun']['color']);

        $user = User::factory()->create();
        $resolved = ChartDisplaySettings::resolve($user);
        $this->assertSame('teal', $resolved['aspects']['trine']['color']);
        $this->assertSame('orange', $resolved['objects']['Sun']['color']);
    }

    public function test_user_personal_settings_override_admin_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->put(route('admin.chart-display.update'), [
            'chart_display' => $this->chartDisplayFormPayload([
                'objects' => [
                    'Mars' => ['enabled' => true, 'color' => 'maroon'],
                ],
            ]),
        ])->assertSessionHasNoErrors();

        $user = User::factory()->create([
            'chart_display_settings' => ChartDisplaySettings::merge(ChartDisplaySettings::adminDefaults(), [
                'objects' => [
                    'Mars' => ['enabled' => true, 'color' => 'crimson'],
                ],
            ]),
        ]);

        $this->assertSame('crimson', ChartDisplaySettings::resolve($user)['objects']['Mars']['color']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function chartDisplayFormPayload(array $overrides = []): array
    {
        $settings = ChartDisplaySettings::merge(ChartDisplaySettings::defaults(), $overrides);
        $form = ['aspects' => [], 'objects' => []];

        foreach ($settings['aspects'] as $key => $item) {
            $form['aspects'][$key] = [
                'enabled' => $item['enabled'] ? '1' : '0',
                'color' => $item['color'],
            ];
        }

        foreach ($settings['objects'] as $key => $item) {
            $form['objects'][$key] = [
                'enabled' => $item['enabled'] ? '1' : '0',
                'color' => $item['color'],
            ];
        }

        return $form;
    }
}
