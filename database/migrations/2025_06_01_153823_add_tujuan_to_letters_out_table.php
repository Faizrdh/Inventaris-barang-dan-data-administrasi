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
        Schema::table('letters_out', function (Blueprint $table) {
            $table->string('tujuan')->after('perihal')->comment('Tujuan surat keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters_out', function (Blueprint $table) {
            $table->dropColumn('tujuan');
        });
    }
};