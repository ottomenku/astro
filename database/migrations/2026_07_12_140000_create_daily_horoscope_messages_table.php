<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_horoscope_messages', function (Blueprint $table) {
            $table->id();
            $table->date('forecast_date');
            $table->string('locale', 8);
            $table->timestamp('chart_datetime_utc');
            $table->json('chart_payload');
            $table->json('score_payload');
            $table->string('scoring_profile_name')->nullable();
            $table->string('motto', 500);
            $table->text('summary');
            $table->text('health');
            $table->text('money');
            $table->text('relationships');
            $table->text('work');
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['forecast_date', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_horoscope_messages');
    }
};
