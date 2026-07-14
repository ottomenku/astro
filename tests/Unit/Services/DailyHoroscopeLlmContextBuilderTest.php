<?php

namespace Tests\Unit\Services;

use App\Services\DailyHoroscopeLlmContextBuilder;
use Tests\TestCase;

class DailyHoroscopeLlmContextBuilderTest extends TestCase
{
    private DailyHoroscopeLlmContextBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = app(DailyHoroscopeLlmContextBuilder::class);
    }

    public function test_chart_context_excludes_houses_and_keeps_dignified_placements(): void
    {
        $payload = [
            'transit' => [
                'datetime_utc' => '2026-07-14T10:00:00+00:00',
                'asc' => 10.0,
                'mc' => 280.0,
                'houses' => [1, 2, 3],
                'planets' => [
                    ['name' => 'Mars', 'sign' => 'Aries', 'sign_degree' => 12.5, 'longitude' => 12.5, 'house' => 1],
                    ['name' => 'Venus', 'sign' => 'Gemini', 'sign_degree' => 4.0, 'longitude' => 64.0, 'house' => 3],
                    ['name' => 'Saturn', 'sign' => 'Aries', 'sign_degree' => 18.0, 'longitude' => 18.0, 'house' => 1],
                ],
                'fixed_stars' => [],
                'aspects' => [],
            ],
        ];

        $context = $this->builder->buildChartContext($payload, 'hu');

        $this->assertArrayNotHasKey('houses', $context);
        $this->assertArrayNotHasKey('asc', $context);
        $this->assertTrue($context['meta']['houses_excluded']);

        $planets = array_column($context['significant_placements'], 'planet');
        $this->assertContains('Mars', $planets);
        $this->assertCount(1, $context['significant_placements']);
        $this->assertSame('Kos', $context['significant_placements'][0]['sign']);
    }

    public function test_aspects_are_ranked_with_signs_and_fixed_stars(): void
    {
        $payload = [
            'transit' => [
                'planets' => [
                    ['name' => 'Mercury', 'sign' => 'Libra', 'sign_degree' => 10.0, 'longitude' => 190.0, 'house' => 7],
                    ['name' => 'Saturn', 'sign' => 'Aries', 'sign_degree' => 12.0, 'longitude' => 12.0, 'house' => 1],
                    ['name' => 'Venus', 'sign' => 'Virgo', 'sign_degree' => 2.0, 'longitude' => 152.0, 'house' => 6],
                ],
                'fixed_stars' => [
                    ['id' => 'Regulus', 'name' => 'Regulus', 'sign' => 'Virgo', 'sign_degree' => 4.0, 'longitude' => 154.0],
                ],
            ],
        ];

        $context = $this->builder->buildChartContext($payload, 'hu');

        $this->assertNotEmpty($context['aspects']);

        $opposition = collect($context['aspects'])->first(fn (array $aspect) => ($aspect['type_key'] ?? '') === 'opposition');
        $this->assertNotNull($opposition);
        $this->assertStringContainsString('Merkúr', (string) $opposition['description']);
        $this->assertStringContainsString('Mérleg', (string) $opposition['body1']['sign']);
        $this->assertStringContainsString('Kos', (string) $opposition['body2']['sign']);
        $this->assertSame(4, $opposition['priority']);

        $starAspect = collect($context['aspects'])->first(fn (array $aspect) => ($aspect['type_key'] ?? '') === 'conjunction'
            && (($aspect['body2']['kind'] ?? '') === 'fixed_star' || ($aspect['body1']['kind'] ?? '') === 'fixed_star'));
        $this->assertNotNull($starAspect);
        $this->assertStringContainsString('Regulus', (string) $starAspect['description']);
    }

    public function test_score_summary_omits_house_breakdown(): void
    {
        $summary = $this->builder->buildScoreSummary([
            'profile' => 'Astro Motto alap pontozás',
            'rating_label' => 'erős',
            'total_score' => 72.5,
            'element_fire' => 10.0,
            'modality_cardinal' => 8.0,
            'breakdown' => [
                'placements' => [['object' => 'Sun', 'house' => 10]],
                'activity_index' => 7.2,
            ],
        ]);

        $this->assertSame('erős', $summary['rating']);
        $this->assertSame(72.5, $summary['total_score']);
        $this->assertSame(7.2, $summary['activity_index']);
        $this->assertArrayNotHasKey('placements', $summary);
        $this->assertArrayNotHasKey('breakdown', $summary);
    }
}
