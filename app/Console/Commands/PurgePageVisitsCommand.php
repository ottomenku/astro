<?php

namespace App\Console\Commands;

use App\Services\PageVisitRetentionService;
use Illuminate\Console\Command;

class PurgePageVisitsCommand extends Command
{
    protected $signature = 'analytics:purge-page-visits';

    protected $description = 'Törli a megőrzési időn túli oldalmegtekintés naplókat';

    public function handle(PageVisitRetentionService $retention): int
    {
        $deleted = $retention->purgeExpired();

        $this->info("Törölve: {$deleted} bejegyzés (megőrzés: {$retention->retentionDays()} nap).");

        return self::SUCCESS;
    }
}
