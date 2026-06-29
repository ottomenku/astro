<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('house_system', 30)->default('placidus')->after('current_lon');
            $table->string('zodiac_mode', 20)->default('tropical')->after('house_system');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['house_system', 'zodiac_mode']);
        });
    }
};
