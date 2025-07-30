<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->integer('copied_from')->nullable()->after('tahun_anggaran');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn('copied_from');
        });
    }

};
