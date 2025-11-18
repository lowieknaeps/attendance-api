<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $t) {
            $t->id();
            $t->string('external_id')->index();  // bv. "557214" uit Dalvin
            $t->string('code')->nullable();      // bv. "WD2"
            $t->string('name');                  // bv. "Web Development 2"
            $t->unsignedBigInteger('teacher_id')->nullable()->index();
            $t->timestamps();
            $t->unique(['external_id','teacher_id']); // zelfde course-id kan bij andere docent bestaan
        });
    }
    public function down(): void { Schema::dropIfExists('courses'); }
};
