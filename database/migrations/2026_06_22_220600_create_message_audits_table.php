<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('thread_id')->nullable()->constrained('chat_threads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // llm | user | app
            $table->string('sender', 20);
            $table->string('sender_name')->nullable();
            $table->string('recipient')->nullable();

            $table->longText('message');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_audits');
    }
};
