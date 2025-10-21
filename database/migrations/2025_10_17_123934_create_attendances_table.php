<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // intern id (voor Laravel)
            $table->unsignedBigInteger('external_id')->index(); // externe id van student
            $table->string('name'); // naam van student
            $table->timestamp('arrived')->nullable(); // aankomsttijd
            $table->string('status'); // aanwezig, afwezig, te laat, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
