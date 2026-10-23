<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $t) {
            $t->text('description')->nullable()->after('tagline');
        });

        // Logo penyelenggara / mitra yang tampil di footer ("Diselenggarakan oleh").
        Schema::create('site_partners', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('logo_path');
            $t->string('url')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_partners');
        Schema::table('site_settings', function (Blueprint $t) {
            $t->dropColumn('description');
        });
    }
};
