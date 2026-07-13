<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('config');
            $table->timestamps();

            $table->index('is_default');
        });

        Schema::create('natal_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('birth_chart_id')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->foreignId('user_horoscope_id')->nullable()->constrained('user_horoscopes')->nullOnDelete();
            $table->timestamp('datetime_utc');
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->boolean('sidereal')->default(false);
            $table->string('ayanamsa', 50)->nullable();
            $table->string('house_system', 30)->default('placidus');
            $table->timestamps();

            $table->unique('birth_chart_id');
            $table->index(['user_id', 'datetime_utc']);
        });

        Schema::create('chart_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('natal_chart_id')->constrained('natal_charts')->cascadeOnDelete();
            $table->string('object', 32);
            $table->string('sign', 32);
            $table->unsignedTinyInteger('house');
            $table->decimal('longitude', 8, 4);
            $table->decimal('sign_degree', 6, 3);
            $table->timestamps();

            $table->unique(['natal_chart_id', 'object']);
        });

        Schema::create('chart_aspects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('natal_chart_id')->constrained('natal_charts')->cascadeOnDelete();
            $table->string('body1', 32);
            $table->string('body2', 32);
            $table->string('aspect_type', 32);
            $table->decimal('orb', 6, 3);
            $table->decimal('strength', 6, 3)->default(1);
            $table->timestamps();

            $table->index(['natal_chart_id', 'aspect_type']);
        });

        Schema::create('chart_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('natal_chart_id')->constrained('natal_charts')->cascadeOnDelete();
            $table->foreignId('scoring_profile_id')->constrained('scoring_profiles')->cascadeOnDelete();
            $table->decimal('polarity_positive', 10, 2)->default(0);
            $table->decimal('polarity_negative', 10, 2)->default(0);
            $table->decimal('polarity_balance', 10, 2)->default(0);
            $table->decimal('element_fire', 10, 2)->default(0);
            $table->decimal('element_earth', 10, 2)->default(0);
            $table->decimal('element_air', 10, 2)->default(0);
            $table->decimal('element_water', 10, 2)->default(0);
            $table->decimal('modality_cardinal', 10, 2)->default(0);
            $table->decimal('modality_fixed', 10, 2)->default(0);
            $table->decimal('modality_mutable', 10, 2)->default(0);
            $table->decimal('total_score', 10, 2)->default(0);
            $table->string('rating_label', 64)->nullable();
            $table->json('breakdown')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['natal_chart_id', 'scoring_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_scores');
        Schema::dropIfExists('chart_aspects');
        Schema::dropIfExists('chart_placements');
        Schema::dropIfExists('natal_charts');
        Schema::dropIfExists('scoring_profiles');
    }
};
