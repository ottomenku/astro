<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_profiles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('engine', 32)->default('integrated')->after('slug');
            $table->string('version', 32)->nullable()->after('engine');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('scoring_profile_id')
                ->nullable()
                ->after('zodiac_mode')
                ->constrained('scoring_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scoring_profile_id');
        });

        Schema::table('scoring_profiles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'engine', 'version']);
        });
    }
};
