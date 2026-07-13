<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_horoscope_settings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 8)->unique();
            $table->text('system_prompt')->nullable();
            $table->text('user_prompt_template')->nullable();
            $table->foreignId('scoring_profile_id')
                ->nullable()
                ->constrained('scoring_profiles')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_horoscope_settings');
    }
};
