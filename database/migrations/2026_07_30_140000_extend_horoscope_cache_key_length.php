<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendCacheKeyColumn('horoscope_daily_messages');
        $this->extendCacheKeyColumn('horoscope_chart_explanations');
    }

    public function down(): void
    {
        $this->shrinkCacheKeyColumn('horoscope_daily_messages');
        $this->shrinkCacheKeyColumn('horoscope_chart_explanations');
    }

    private function extendCacheKeyColumn(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'cache_key')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE {$tableName} MODIFY cache_key VARCHAR(191) NOT NULL");
        } elseif ($driver === 'sqlite') {
            // Tests use sqlite :memory: — column is already wide enough there.
        }
    }

    private function shrinkCacheKeyColumn(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'cache_key')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE {$tableName} MODIFY cache_key VARCHAR(64) NOT NULL");
        }
    }
};
