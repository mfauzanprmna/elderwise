<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $t) {
            // Lencana per modul: ikon emoji ATAU logo unggahan, plus warna medali.
            $t->string('badge_icon', 16)->nullable()->after('badge');
            $t->string('badge_image')->nullable()->after('badge_icon');
            $t->string('badge_color', 20)->default('gold')->after('badge_image');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $t) {
            $t->dropColumn(['badge_icon', 'badge_image', 'badge_color']);
        });
    }
};
