<?php

use App\Support\UiTemplateCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_ui_settings', function (Blueprint $table) {
            $table->id();
            $table->string('active_template', 32)->default(UiTemplateCatalog::CLASSIC);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_ui_settings');
    }
};
