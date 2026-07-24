<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('horoscope_daily_messages')) {
            return;
        }

        if (! Schema::hasColumn('horoscope_daily_messages', 'period_type')) {
            Schema::table('horoscope_daily_messages', function (Blueprint $table) {
                $table->string('period_type', 16)->default('daily')->after('locale');
                $table->date('period_start')->nullable()->after('period_type');
                $table->date('period_end')->nullable()->after('period_start');
                $table->json('period_context')->nullable()->after('context_payload');
                $table->text('explanation')->nullable()->after('work');
            });
        }

        DB::table('horoscope_daily_messages')
            ->whereNull('period_start')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('horoscope_daily_messages')->where('id', $row->id)->update([
                        'period_start' => $row->forecast_date,
                        'period_end' => $row->forecast_date,
                    ]);
                }
            });

        if (! $this->indexExists('horoscope_daily_messages', 'hdm_user_locale_key_period_uq')) {
            Schema::table('horoscope_daily_messages', function (Blueprint $table) {
                $table->unique(
                    ['user_id', 'locale', 'cache_key', 'period_type', 'period_start'],
                    'hdm_user_locale_key_period_uq',
                );
            });
        }

        if (! $this->indexExists('horoscope_daily_messages', 'hdm_user_id_idx')) {
            Schema::table('horoscope_daily_messages', function (Blueprint $table) {
                $table->index('user_id', 'hdm_user_id_idx');
            });
        }

        if ($this->indexExists('horoscope_daily_messages', 'hdm_user_date_locale_key_uq')) {
            Schema::table('horoscope_daily_messages', function (Blueprint $table) {
                $table->dropUnique('hdm_user_date_locale_key_uq');
            });
        }
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
