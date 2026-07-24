<?php

namespace Tests\Feature\Admin;

use App\Models\PageVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_visit_is_logged(): void
    {
        $this->get(route('home'))->assertOk();

        $this->assertDatabaseCount('page_visits', 1);

        $visit = PageVisit::query()->first();
        $this->assertSame('home', $visit->route_name);
        $this->assertSame('Nyitólap', $visit->page_label);
        $this->assertFalse($visit->is_bot);
    }

    public function test_logged_in_user_is_linked_in_page_visit(): void
    {
        $user = User::factory()->create([
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
        ]);

        $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertDatabaseHas('page_visits', [
            'user_id' => $user->id,
            'user_name' => 'Teszt Elek',
            'user_email' => 'teszt@example.com',
        ]);
    }

    public function test_admin_can_view_logs_and_summary(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        PageVisit::query()->create([
            'visited_at' => now(),
            'ip_address' => '127.0.0.1',
            'path' => '/',
            'page_label' => 'Nyitólap',
            'route_name' => 'home',
            'method' => 'GET',
            'status_code' => 200,
            'is_bot' => false,
            'visitor_type' => 'human',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-visits.logs'))
            ->assertOk()
            ->assertSee('Nyitólap');

        $this->actingAs($admin)
            ->get(route('admin.page-visits.summary'))
            ->assertOk()
            ->assertSee('Látogatás összesítő');
    }

    public function test_retention_setting_purges_old_visits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        PageVisit::query()->create([
            'visited_at' => now()->subDays(10),
            'ip_address' => '1.1.1.1',
            'path' => '/',
            'page_label' => 'Régi',
            'method' => 'GET',
            'is_bot' => false,
            'visitor_type' => 'human',
        ]);

        PageVisit::query()->create([
            'visited_at' => now()->subDay(),
            'ip_address' => '2.2.2.2',
            'path' => '/',
            'page_label' => 'Friss',
            'method' => 'GET',
            'is_bot' => false,
            'visitor_type' => 'human',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.page-visits.settings.update'), [
                'retention_days' => 7,
            ])
            ->assertRedirect(route('admin.page-visits.summary'));

        $this->assertDatabaseCount('page_visits', 1);
        $this->assertDatabaseHas('page_visits', [
            'ip_address' => '2.2.2.2',
        ]);
    }
}
