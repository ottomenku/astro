<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MESSAGES_UNIQUE = 'udhm_user_date_locale_uq';

    public function up(): void
    {
        if (! Schema::hasTable('user_daily_horoscope_settings')) {
            Schema::create('user_daily_horoscope_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->boolean('use_personal_daily')->default(false);
                $table->text('system_prompt')->nullable();
                $table->text('user_prompt_template')->nullable();
                $table->foreignId('scoring_profile_id')->nullable()->constrained('scoring_profiles')->nullOnDelete();
                $table->foreignId('birth_chart_id')->nullable()->constrained('birth_charts')->nullOnDelete();
                $table->foreignId('user_horoscope_id')->nullable()->constrained('user_horoscopes')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_daily_horoscope_messages')) {
            Schema::create('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('forecast_date');
                $table->string('locale', 8);
                $table->timestamp('chart_datetime_utc');
                $table->json('chart_payload');
                $table->json('score_payload');
                $table->json('attached_chart_payload')->nullable();
                $table->string('scoring_profile_name')->nullable();
                $table->string('motto', 500);
                $table->text('summary');
                $table->text('health');
                $table->text('money');
                $table->text('relationships');
                $table->text('work');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'forecast_date', 'locale'], self::MESSAGES_UNIQUE);
            });

            return;
        }

        if (! $this->indexExists('user_daily_horoscope_messages', self::MESSAGES_UNIQUE)) {
            Schema::table('user_daily_horoscope_messages', function (Blueprint $table) {
                $table->unique(['user_id', 'forecast_date', 'locale'], self::MESSAGES_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_horoscope_messages');
        Schema::dropIfExists('user_daily_horoscope_settings');
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
