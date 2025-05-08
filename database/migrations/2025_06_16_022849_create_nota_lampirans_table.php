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
        Schema::create('nota_lampirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_dinas_id')->constrained()->onDelete('cascade');
            $table->string('nama_file');
            $table->string('path');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_lampirans');
    }
};
