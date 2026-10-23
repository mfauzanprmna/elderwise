<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $t) {
            $t->id();
            $t->string('site_name')->default('ElderWiSE');
            $t->string('tagline')->nullable();
            $t->string('footer_text')->nullable();
            $t->string('logo_path')->nullable();
            $t->string('favicon_path')->nullable();
            $t->boolean('show_name_with_logo')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
