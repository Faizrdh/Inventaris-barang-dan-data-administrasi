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
        Schema::table('letters_in', function (Blueprint $table) {
            // Tambah kolom file dari letters
            $table->string('file_name')->nullable()->after('notes');
            $table->string('file_path')->nullable()->after('file_name');
            $table->bigInteger('file_size')->nullable()->after('file_path');
            $table->string('file_type')->nullable()->after('file_size');
            
            // Tambah index
            $table->index('file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters_in', function (Blueprint $table) {
            // Hapus kolom file
            $table->dropIndex(['file_name']);
            $table->dropColumn(['file_name', 'file_path', 'file_size', 'file_type']);
        });
    }
};