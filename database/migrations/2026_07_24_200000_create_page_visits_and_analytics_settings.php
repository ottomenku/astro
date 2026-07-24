<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('retention_days')->default(7);
            $table->timestamps();
        });

        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->timestamp('visited_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('ip_address', 45);
            $table->string('route_name')->nullable();
            $table->string('path', 2048);
            $table->string('page_label')->nullable();
            $table->string('method', 10)->default('GET');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name')->nullable();
            $table->string('visitor_type', 20)->default('human');
            $table->text('user_agent')->nullable();
            $table->string('referer', 2048)->nullable();
            $table->string('accept_language', 255)->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('browser_version', 30)->nullable();
            $table->string('platform', 60)->nullable();
            $table->string('platform_version', 30)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('country_name', 100)->nullable();
            $table->string('region', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('timezone', 60)->nullable();
            $table->string('session_id', 128)->nullable();
            $table->timestamps();

            $table->index('visited_at');
            $table->index(['route_name', 'visited_at']);
            $table->index(['ip_address', 'visited_at']);
            $table->index(['user_id', 'visited_at']);
            $table->index(['is_bot', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
        Schema::dropIfExists('analytics_settings');
    }
};
