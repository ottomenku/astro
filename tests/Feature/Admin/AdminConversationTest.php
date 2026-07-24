<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_conversation_details_with_tokens(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => 'Teszt User']);

        $conversation = Conversation::query()->create([
            'user_id' => $user->id,
            'model' => 'gpt-4o-mini',
            'prompt' => 'Mi a mai nap üzenete?',
            'response' => str_repeat('Hosszú válasz szöveg. ', 40),
            'meta' => [
                'openai' => [
                    'usage' => [
                        'prompt_tokens' => 120,
                        'completion_tokens' => 340,
                        'total_tokens' => 460,
                    ],
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Konverzáció #'.$conversation->id)
            ->assertSee('Mi a mai nap üzenete?')
            ->assertSee('Hosszú válasz szöveg.')
            ->assertSee('460')
            ->assertSee('120')
            ->assertSee('340');

        $this->actingAs($admin)
            ->get(route('admin.conversations.index'))
            ->assertOk()
            ->assertSee('460')
            ->assertSee('Részletek');
    }
}
