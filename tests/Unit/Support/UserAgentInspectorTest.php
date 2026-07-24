<?php

namespace Tests\Unit\Support;

use App\Support\UserAgentInspector;
use Tests\TestCase;

class UserAgentInspectorTest extends TestCase
{
    public function test_detects_googlebot(): void
    {
        $inspector = new UserAgentInspector;

        $result = $inspector->inspect('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $this->assertTrue($result['is_bot']);
        $this->assertSame('Googlebot', $result['bot_name']);
        $this->assertSame('bot', $result['visitor_type']);
    }

    public function test_detects_human_chrome_on_windows(): void
    {
        $inspector = new UserAgentInspector;

        $result = $inspector->inspect('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $this->assertFalse($result['is_bot']);
        $this->assertSame('human', $result['visitor_type']);
        $this->assertSame('desktop', $result['device_type']);
        $this->assertSame('Chrome', $result['browser']);
        $this->assertSame('Windows', $result['platform']);
    }
}
