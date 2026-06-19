<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tier', 20)->default('base')->after('password');
            $table->boolean('is_admin')->default(false)->after('tier');

            $table->unsignedBigInteger('token_quota_total')->default(0)->after('is_admin');
            $table->unsignedBigInteger('token_quota_used')->default(0)->after('token_quota_total');
            $table->timestamp('token_quota_reset_at')->nullable()->after('token_quota_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tier',
                'is_admin',
                'token_quota_total',
                'token_quota_used',
                'token_quota_reset_at',
            ]);
        });
    }
};
