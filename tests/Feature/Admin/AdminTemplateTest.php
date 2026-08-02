<?php

namespace Tests\Feature\Admin;

use App\Models\SiteUiSetting;
use App\Models\User;
use App\Support\UiTemplateCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_template_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.templates.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_template_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertSee(__('app.admin_templates'))
            ->assertSee(__('app.template_classic_name'))
            ->assertSee(__('app.template_aurora_name'))
            ->assertSee(__('app.template_activate'));
    }

    public function test_admin_can_activate_classic_template(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.templates.update'), [
                'template' => UiTemplateCatalog::CLASSIC,
            ])
            ->assertRedirect(route('admin.templates.index'))
            ->assertSessionHas('status', __('app.template_switched'));

        $this->assertSame(
            UiTemplateCatalog::CLASSIC,
            SiteUiSetting::current()->active_template
        );
    }

    public function test_admin_can_activate_aurora_template(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.templates.update'), [
                'template' => UiTemplateCatalog::AURORA,
            ])
            ->assertRedirect(route('admin.templates.index'))
            ->assertSessionHas('status', __('app.template_switched'));

        $this->assertSame(
            UiTemplateCatalog::AURORA,
            SiteUiSetting::current()->active_template
        );
    }

    public function test_home_uses_aurora_template_when_active(): void
    {
        SiteUiSetting::current()->update(['active_template' => UiTemplateCatalog::AURORA]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('public.aurora_own_question_btn'))
            ->assertSee(__('public.aurora_topics_heading'));
    }

    public function test_horoscope_uses_aurora_template_when_active(): void
    {
        SiteUiSetting::current()->update(['active_template' => UiTemplateCatalog::AURORA]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('horoscope.index'))
            ->assertOk()
            ->assertSee('auroraPositionsSection', false)
            ->assertSee(__('public.aurora_nav_chart'))
            ->assertSee(__('public.aurora_planet_positions'))
            ->assertSee(__('horoscope.aspects_tab'));
    }
}
