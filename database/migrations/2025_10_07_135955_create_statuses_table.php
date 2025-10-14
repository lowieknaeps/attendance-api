<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->boolean('present');
            $table->timestampTz('occurred_at');
            $table->timestamps();
            $table->unique(['student_id','course_id','occurred_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('statuses'); }
};

