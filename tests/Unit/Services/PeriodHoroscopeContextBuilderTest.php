<?php

namespace Tests\Unit\Services;

use App\Services\HoroscopeCalculator;
use App\Services\PeriodHoroscopeContextBuilder;
use App\Support\HoroscopePeriod;
use Mockery;
use Tests\TestCase;

class PeriodHoroscopeContextBuilderTest extends TestCase
{
    public function test_build_includes_opening_closing_positions_and_retrograde_windows(): void
    {
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::WEEKLY);
        $mockChart = [
            'natal' => [
                'planets' => [
                    ['name' => 'Mercury', 'sign' => 'Leo', 'sign_degree' => 10.0, 'retrograde' => true],
                    ['name' => 'Mars', 'sign' => 'Aries', 'sign_degree' => 5.0, 'retrograde' => false],
                ],
            ],
        ];

        $this->mock(HoroscopeCalculator::class, function ($mock) use ($mockChart) {
            $mock->shouldReceive('calculate')->andReturn($mockChart);
        });

        $context = app(PeriodHoroscopeContextBuilder::class)->build(
            HoroscopePeriod::WEEKLY,
            $bounds['start'],
            $bounds['end'],
            'hu',
        );

        $this->assertSame(HoroscopePeriod::WEEKLY, $context['period_type']);
        $this->assertArrayHasKey('opening', $context);
        $this->assertArrayHasKey('closing', $context);
        $this->assertSame('Mercury', $context['opening']['positions'][0]['planet']);
        $this->assertTrue($context['opening']['positions'][0]['retrograde']);
        $this->assertNotEmpty($context['retrograde_windows']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
