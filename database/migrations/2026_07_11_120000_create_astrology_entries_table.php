<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('astrology_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('key', 64);
            $table->string('locale', 8);
            $table->string('title', 120);
            $table->text('question');
            $table->text('answer');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'key', 'locale']);
            $table->index(['locale', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('astrology_entries');
    }
};
