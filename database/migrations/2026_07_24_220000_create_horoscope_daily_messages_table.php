<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horoscope_daily_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('forecast_date');
            $table->string('locale', 8);
            $table->string('kind', 16);
            $table->string('cache_key', 64);
            $table->foreignId('birth_chart_id')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->foreignId('birth_chart_id_a')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->foreignId('birth_chart_id_b')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->timestamp('chart_datetime_utc');
            $table->json('chart_payload');
            $table->json('score_payload')->nullable();
            $table->json('context_payload')->nullable();
            $table->string('scoring_profile_name')->nullable();
            $table->string('motto', 500);
            $table->text('summary');
            $table->text('health');
            $table->text('money');
            $table->text('relationships');
            $table->text('work');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'forecast_date', 'locale', 'cache_key'], 'hdm_user_date_locale_key_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horoscope_daily_messages');
    }
};
