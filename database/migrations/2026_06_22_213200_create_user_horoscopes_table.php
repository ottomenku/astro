<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_horoscopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // natál / (később: child, event, stb.)
            $table->string('kind', 30)->default('natal');
            $table->string('label')->default('Natál');

            $table->boolean('sidereal')->default(false);
            $table->string('ayanamsa', 50)->nullable();
            $table->string('house_system', 30)->default('placidus');

            $table->json('data');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_horoscopes');
    }
};
