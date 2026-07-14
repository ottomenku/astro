<?php

use Database\Seeders\AstrologyScoringProfileSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('scoring_profiles')) {
            return;
        }

        (new AstrologyScoringProfileSeeder)->run();
    }

    public function down(): void
    {
        // Seeded profiles are kept on rollback.
    }
};
