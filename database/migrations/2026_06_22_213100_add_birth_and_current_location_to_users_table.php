<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Születési adatok (UTC-ben tárolva, de az offsetet is megőrizzük)
            $table->dateTime('birth_datetime_utc')->nullable()->after('password');
            $table->decimal('birth_tz_offset', 4, 2)->nullable()->after('birth_datetime_utc');
            $table->string('birth_place_label')->nullable()->after('birth_tz_offset');
            $table->decimal('birth_lat', 9, 6)->nullable()->after('birth_place_label');
            $table->decimal('birth_lon', 9, 6)->nullable()->after('birth_lat');

            // Jelenlegi hely (tranzitokhoz) – később frissíthető profilból / geolokációból
            $table->decimal('current_tz_offset', 4, 2)->nullable()->after('birth_lon');
            $table->string('current_place_label')->nullable()->after('current_tz_offset');
            $table->decimal('current_lat', 9, 6)->nullable()->after('current_place_label');
            $table->decimal('current_lon', 9, 6)->nullable()->after('current_lat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birth_datetime_utc',
                'birth_tz_offset',
                'birth_place_label',
                'birth_lat',
                'birth_lon',
                'current_tz_offset',
                'current_place_label',
                'current_lat',
                'current_lon',
            ]);
        });
    }
};
