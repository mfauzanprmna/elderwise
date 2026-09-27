<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('modules', function(Blueprint $t){$t->id();$t->unsignedInteger('number');$t->string('title');$t->string('slogan');$t->text('description')->nullable();$t->string('badge')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();}); } public function down(): void { Schema::dropIfExists('modules'); } };
