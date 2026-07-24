<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('daily_horoscope_messages', 'period_type')) {
            return;
        }

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

        if (! $this->indexExists('user_daily_horoscope_messages', 'udhm_user_locale_period_uq')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->unique(['user_id', 'locale', 'period_type', 'period_start'], 'udhm_user_locale_period_uq');
            });
        }

        if (! $this->indexExists('user_daily_horoscope_messages', 'udhm_user_id_idx')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->index('user_id', 'udhm_user_id_idx');
            });
        }

        if ($this->indexExists('user_daily_horoscope_messages', 'udhm_user_date_locale_uq')) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->dropUnique('udhm_user_date_locale_uq');
            });
        }

        DB::table('daily_horoscope_messages')
            ->whereNull('period_start')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('daily_horoscope_messages')->where('id', $row->id)->update([
                        'period_start' => $row->forecast_date,
                        'period_end' => $row->forecast_date,
                    ]);
                }
            });

        DB::table('user_daily_horoscope_messages')
            ->whereNull('period_start')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('user_daily_horoscope_messages')->where('id', $row->id)->update([
                        'period_start' => $row->forecast_date,
                        'period_end' => $row->forecast_date,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Repair migration – no rollback.
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
