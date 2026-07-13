<?php

namespace App\Console\Commands;

use App\Models\BirthChart;
use App\Services\AstrologyChartScoringService;
use Illuminate\Console\Command;

class ScoreBirthChartsCommand extends Command
{
    protected $signature = 'astrology:score-charts {--user= : Csak egy adott user ID}';

    protected $description = 'Kiszámítja és elmenti az értékelést minden születési adathoz';

    public function handle(AstrologyChartScoringService $scoring): int
    {
        $query = BirthChart::query()->with('user');

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $count = 0;
        $failed = 0;

        $query->orderBy('id')->each(function (BirthChart $chart) use ($scoring, &$count, &$failed) {
            try {
                $scoring->scoreBirthChart($chart);
                $count++;
                $this->line("OK #{$chart->id} – {$chart->name}");
            } catch (\Throwable $error) {
                $failed++;
                $this->error("HIBA #{$chart->id}: {$error->getMessage()}");
            }
        });

        $this->info("Kész: {$count} értékelt, {$failed} hiba.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
