<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('number')->unique();
            $t->timestamp('issued_at');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
