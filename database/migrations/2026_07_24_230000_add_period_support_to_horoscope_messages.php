<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addPeriodColumns('daily_horoscope_messages');
        $this->addPeriodColumns('user_daily_horoscope_messages');

        $this->backfillPeriodDates('daily_horoscope_messages');
        $this->backfillPeriodDates('user_daily_horoscope_messages');

        if (! $this->indexExists('daily_horoscope_messages', 'dhm_locale_period_uq')) {
            Schema::table('daily_horoscope_messages', function (Blueprint $table) {
                $table->unique(['locale', 'period_type', 'period_start'], 'dhm_locale_period_uq');
            });
        }

        if ($this->indexExists('daily_horoscope_messages', 'daily_horoscope_messages_forecast_date_locale_unique')) {
            Schema::table('daily_horoscope_messages', function (Blueprint $table) {
                $table->dropUnique(['forecast_date', 'locale']);
            });
        }

        if (! $this->indexExists('user_daily_horoscope_messages', 'udhm_user_id_idx')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->index('user_id', 'udhm_user_id_idx');
            });
        }

        if (! $this->indexExists('user_daily_horoscope_messages', 'udhm_user_locale_period_uq')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->unique(['user_id', 'locale', 'period_type', 'period_start'], 'udhm_user_locale_period_uq');
            });
        }

        if ($this->indexExists('user_daily_horoscope_messages', 'udhm_user_date_locale_uq')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->dropUnique('udhm_user_date_locale_uq');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_daily_horoscope_messages')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                if ($this->indexExists('user_daily_horoscope_messages', 'udhm_user_locale_period_uq')) {
                    $table->dropUnique('udhm_user_locale_period_uq');
                }
                if (! $this->indexExists('user_daily_horoscope_messages', 'udhm_user_date_locale_uq')) {
                    $table->unique(['user_id', 'forecast_date', 'locale'], 'udhm_user_date_locale_uq');
                }
                if ($this->indexExists('user_daily_horoscope_messages', 'udhm_user_id_idx')) {
                    $table->dropIndex('udhm_user_id_idx');
                }
            });

            $this->dropPeriodColumns('user_daily_horoscope_messages');
        }

        if (Schema::hasTable('daily_horoscope_messages')) {
            Schema::table('daily_horoscope_messages', function (Blueprint $table) {
                if ($this->indexExists('daily_horoscope_messages', 'dhm_locale_period_uq')) {
                    $table->dropUnique('dhm_locale_period_uq');
                }
                if (! $this->indexExists('daily_horoscope_messages', 'daily_horoscope_messages_forecast_date_locale_unique')) {
                    $table->unique(['forecast_date', 'locale']);
                }
            });

            $this->dropPeriodColumns('daily_horoscope_messages');
        }
    }

    private function addPeriodColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'period_type')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('period_type', 16)->default('daily')->after('locale');
            $table->date('period_start')->nullable()->after('period_type');
            $table->date('period_end')->nullable()->after('period_start');
            $table->json('period_context')->nullable()->after('chart_payload');
        });
    }

    private function dropPeriodColumns(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'period_type')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['period_type', 'period_start', 'period_end', 'period_context']);
        });
    }

    private function backfillPeriodDates(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'period_start')) {
            return;
        }

        DB::table($tableName)
            ->whereNull('period_start')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($tableName): void {
                foreach ($rows as $row) {
                    DB::table($tableName)->where('id', $row->id)->update([
                        'period_start' => $row->forecast_date,
                        'period_end' => $row->forecast_date,
                    ]);
                }
            });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};
