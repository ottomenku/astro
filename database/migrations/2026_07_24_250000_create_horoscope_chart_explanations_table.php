<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horoscope_chart_explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('kind', 16);
            $table->string('cache_key', 64);
            $table->foreignId('birth_chart_id')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->foreignId('birth_chart_id_a')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->foreignId('birth_chart_id_b')->nullable()->constrained('birth_charts')->nullOnDelete();
            $table->json('context_payload')->nullable();
            $table->text('explanation');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'locale', 'cache_key'], 'hce_user_locale_key_uq');
            $table->index('user_id', 'hce_user_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horoscope_chart_explanations');
    }
};
