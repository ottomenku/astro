<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('astrology_entries', function (Blueprint $table) {
            $table->unsignedInteger('click_count')->default(0)->after('created_by_user_id');
            $table->foreignId('first_clicked_by_user_id')->nullable()->after('click_count')->constrained('users')->nullOnDelete();
            $table->timestamp('first_clicked_at')->nullable()->after('first_clicked_by_user_id');
            $table->timestamp('last_clicked_at')->nullable()->after('first_clicked_at');
        });

        DB::table('astrology_entries')->orderBy('id')->chunkById(100, function ($entries): void {
            foreach ($entries as $entry) {
                DB::table('astrology_entries')->where('id', $entry->id)->update([
                    'click_count' => 1,
                    'first_clicked_by_user_id' => $entry->created_by_user_id,
                    'first_clicked_at' => $entry->created_at,
                    'last_clicked_at' => $entry->created_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('astrology_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('first_clicked_by_user_id');
            $table->dropColumn(['click_count', 'first_clicked_at', 'last_clicked_at']);
        });
    }
};
