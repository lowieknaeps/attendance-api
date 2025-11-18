<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('groups', function (Blueprint $t) {
            $t->id();
            $t->string('external_id')->nullable()->index(); // indien Dalvin group-id heeft
            $t->string('code')->index();                    // bv. "MCT2A"
            $t->string('name')->nullable();                 // friendly name (optioneel)
            $t->timestamps();
            $t->unique(['external_id']); // als je external_id gebruikt
        });
    }
    public function down(): void { Schema::dropIfExists('groups'); }
};