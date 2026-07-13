<?php

namespace Tests\Unit\Services;

use App\Services\ChatPrompts;
use Tests\TestCase;

class ChatPromptsTest extends TestCase
{
    public function test_thread_system_prompt_is_english_when_locale_is_en(): void
    {
        $prompt = ChatPrompts::threadSystem('en');

        $this->assertStringContainsString('Reply in English', $prompt);
        $this->assertStringNotContainsString('magyarul', strtolower($prompt));
    }

    public function test_thread_system_prompt_is_hungarian_when_locale_is_hu(): void
    {
        $prompt = ChatPrompts::threadSystem('hu');

        $this->assertStringContainsString('magyarul', strtolower($prompt));
    }

    public function test_horoscope_system_prompt_uses_english_chart_context_label(): void
    {
        $prompt = ChatPrompts::horoscopeSystem(['planets' => []], null, 'en');

        $this->assertStringContainsString('Current chart data (JSON):', $prompt);
        $this->assertStringContainsString('Reply in English', $prompt);
    }
}
