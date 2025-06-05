<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nota_dinas_relasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_dinas_id')->constrained('nota_dinas')->onDelete('cascade');
            $table->foreignId('terkait_id')->constrained('nota_dinas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['nota_dinas_id', 'terkait_id']); // Hindari duplikasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_dinas_relasis');
    }
};
