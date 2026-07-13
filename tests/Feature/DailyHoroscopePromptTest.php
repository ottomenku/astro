<?php

namespace Tests\Feature;

use App\Models\DailyHoroscopeSetting;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Models\UserDailyHoroscopeSetting;
use App\Services\DailyHoroscopePromptBuilder;
use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyHoroscopePromptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AstrologyScoringProfileSeeder::class);
    }

    public function test_user_prompt_appends_data_when_placeholders_missing(): void
    {
        $profile = ScoringProfile::query()->firstOrFail();
        DailyHoroscopeSetting::forLocale('hu')->update([
            'user_prompt_template' => 'Írj konkrét napi horoszkópot.',
            'scoring_profile_id' => $profile->id,
        ]);

        $chart = ['natal' => ['planets' => [['name' => 'Sun', 'sign' => 'Leo']]]];
        $score = ['profile' => 'Teszt', 'rating_label' => 'erős'];

        $prompt = app(DailyHoroscopePromptBuilder::class)->userPrompt('hu', $chart, $score);

        $this->assertStringContainsString('Írj konkrét napi horoszkópot.', $prompt);
        $this->assertStringContainsString('"Sun"', $prompt);
        $this->assertStringContainsString('"erős"', $prompt);
        $this->assertStringContainsString('automatikusan csatolva', $prompt);
    }

    public function test_system_prompt_always_includes_output_format(): void
    {
        DailyHoroscopeSetting::forLocale('hu')->update([
            'system_prompt' => 'Egyedi asztrológiai utasítás.',
        ]);

        $full = app(DailyHoroscopePromptBuilder::class)->globalSystemPrompt('hu');

        $this->assertStringContainsString('Egyedi asztrológiai utasítás.', $full);
        $this->assertStringContainsString('KIMENETI FORMÁTUM', $full);
        $this->assertStringContainsString('"motto"', $full);
    }

    public function test_user_prompt_append_is_included(): void
    {
        DailyHoroscopeSetting::forLocale('hu')->update([
            'user_prompt_append' => 'Legyen optimista, de konkrét.',
        ]);

        $chart = ['natal' => ['planets' => [['name' => 'Sun', 'sign' => 'Leo']]]];
        $score = ['rating_label' => 'erős'];

        $prompt = app(DailyHoroscopePromptBuilder::class)->globalUserPrompt('hu', $chart, $score);

        $this->assertStringContainsString('Legyen optimista, de konkrét.', $prompt);
        $this->assertStringContainsString('"Sun"', $prompt);
    }

    public function test_admin_can_update_generation_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $profiles = ScoringProfile::query()->orderBy('id')->get();
        $astroMotto = $profiles->first(fn (ScoringProfile $p) => $p->isAstroMotto());

        $this->actingAs($admin)
            ->put(route('admin.daily-horoscope.generation.update'), [
                'locale' => 'hu',
                'user_prompt_append' => 'Rövid, lényegre törő válasz.',
                'scoring_profile_id' => $astroMotto->id,
                'auto_publish' => '1',
            ])
            ->assertRedirect(route('admin.daily-horoscope.edit', ['locale' => 'hu', 'tab' => 'prompt']));

        $setting = DailyHoroscopeSetting::forLocale('hu');
        $this->assertSame('Rövid, lényegre törő válasz.', $setting->user_prompt_append);
        $this->assertSame($astroMotto->id, $setting->scoring_profile_id);
        $this->assertTrue($setting->auto_publish);
    }

    public function test_admin_can_update_system_prompt(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.daily-horoscope.system.update'), [
                'locale' => 'hu',
                'system_prompt' => 'Saját system utasítás.',
            ])
            ->assertRedirect(route('admin.daily-horoscope.edit', ['locale' => 'hu', 'tab' => 'system']));

        $this->assertSame('Saját system utasítás.', DailyHoroscopeSetting::forLocale('hu')->system_prompt);
    }

    public function test_user_can_save_personal_daily_settings(): void
    {
        $user = User::factory()->create();
        $profile = ScoringProfile::query()->firstOrFail();

        $this->actingAs($user)
            ->patch(route('profile.daily-horoscope.update'), [
                'use_personal_daily' => '1',
                'scoring_profile_id' => $profile->id,
                'attached_source' => 'none',
                'system_prompt' => 'Személyes system.',
                'user_prompt_template' => 'Személyes user.',
            ])
            ->assertRedirect(route('profile.daily-horoscope.edit'));

        $setting = UserDailyHoroscopeSetting::forUser($user);
        $this->assertTrue($setting->use_personal_daily);
        $this->assertSame('Személyes system.', $setting->system_prompt);
    }
}
