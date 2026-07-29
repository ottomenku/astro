<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('explanation_sentences_short')->default(20)->after('auto_publish');
            $table->unsignedSmallInteger('explanation_sentences_normal')->default(50)->after('explanation_sentences_short');
            $table->unsignedSmallInteger('explanation_sentences_detailed')->default(100)->after('explanation_sentences_normal');
            $table->unsignedSmallInteger('message_sentences_short')->default(20)->after('explanation_sentences_detailed');
            $table->unsignedSmallInteger('message_sentences_normal')->default(50)->after('message_sentences_short');
            $table->unsignedSmallInteger('message_sentences_detailed')->default(100)->after('message_sentences_normal');
            $table->text('horoscope_prompt_personal_message')->nullable()->after('message_sentences_detailed');
            $table->text('horoscope_prompt_partnership_message')->nullable()->after('horoscope_prompt_personal_message');
            $table->text('horoscope_prompt_personal_explanation')->nullable()->after('horoscope_prompt_partnership_message');
            $table->text('horoscope_prompt_partnership_explanation')->nullable()->after('horoscope_prompt_personal_explanation');
        });
    }

    public function down(): void
    {
        Schema::table('daily_horoscope_settings', function (Blueprint $table) {
            $table->dropColumn([
                'explanation_sentences_short',
                'explanation_sentences_normal',
                'explanation_sentences_detailed',
                'message_sentences_short',
                'message_sentences_normal',
                'message_sentences_detailed',
                'horoscope_prompt_personal_message',
                'horoscope_prompt_partnership_message',
                'horoscope_prompt_personal_explanation',
                'horoscope_prompt_partnership_explanation',
            ]);
        });
    }
};
