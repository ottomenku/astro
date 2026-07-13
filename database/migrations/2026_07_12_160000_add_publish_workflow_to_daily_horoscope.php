<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->boolean('auto_publish')->default(false)->after('scoring_profile_id');
        });

        Schema::table('daily_horoscope_messages', function (Blueprint $table) {
            $table->string('status', 16)->default('draft')->after('locale');
            $table->timestamp('published_at')->nullable()->after('generated_at');
            $table->foreignId('approved_by_user_id')->nullable()->after('published_at')
                ->constrained('users')->nullOnDelete();
        });

        DB::table('daily_horoscope_messages')
            ->whereNotNull('generated_at')
            ->update([
                'status' => 'published',
                'published_at' => DB::raw('generated_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('daily_horoscope_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn(['status', 'published_at']);
        });

        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->dropColumn('auto_publish');
        });
    }
};
