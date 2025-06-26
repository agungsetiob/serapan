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
        // Schema::create('nota_dinas', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('nomor_nota');
        //     $table->string('perihal');
        //     $table->decimal('anggaran', 15,2);
        //     $table->date('tanggal_pengajuan');
        //     $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatans')->onDelete('cascade');
        //     $table->timestamps();
        // });
        Schema::create('nota_dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota');
            $table->string('perihal');
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->date('tanggal_pengajuan');
            $table->foreignId('sub_kegiatan_id')->nullable()->constrained('sub_kegiatans')->onDelete('cascade');
            $table->foreignId('skpd_id')->nullable()->constrained('skpds')->onDelete('cascade');
            $table->enum('jenis', [
                'Pelaksanaan', 'GU', 'TU', 'LS', 'Perda', 'Perbup', 'SK', 'Rekomendasi', 'Surat', 'Telaah', 'Edaran', 'Instruksi'
                ])->default('Pelaksanaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_dinas');
    }
};
