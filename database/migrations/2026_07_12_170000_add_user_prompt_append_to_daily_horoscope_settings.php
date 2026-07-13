<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->text('user_prompt_append')->nullable()->after('user_prompt_template');
        });
    }

    public function down(): void
    {
        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->dropColumn('user_prompt_append');
        });
    }
};
