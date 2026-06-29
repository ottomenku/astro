<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birth_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('gender', 10); // male | female
            $table->dateTime('birth_datetime_utc');
            $table->decimal('birth_tz_offset', 4, 2);
            $table->string('birth_place_label')->nullable();
            $table->decimal('birth_lat', 9, 6)->nullable();
            $table->decimal('birth_lon', 9, 6)->nullable();
            $table->unsignedTinyInteger('time_accuracy'); // 1–5
            $table->dateTime('corrected_datetime_utc')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        if (Schema::hasColumn('users', 'birth_datetime_utc')) {
            $users = DB::table('users')
                ->whereNotNull('birth_datetime_utc')
                ->orderBy('id')
                ->get();

            foreach ($users as $user) {
                DB::table('birth_charts')->insert([
                    'user_id' => $user->id,
                    'name' => $user->name ?? 'Saját',
                    'gender' => 'male',
                    'birth_datetime_utc' => $user->birth_datetime_utc,
                    'birth_tz_offset' => $user->birth_tz_offset ?? 0,
                    'birth_place_label' => $user->birth_place_label,
                    'birth_lat' => $user->birth_lat,
                    'birth_lon' => $user->birth_lon,
                    'time_accuracy' => 3,
                    'corrected_datetime_utc' => null,
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('birth_charts');
    }
};
