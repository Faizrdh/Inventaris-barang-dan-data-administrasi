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
            // Menambahkan field baru
            $table->string('perihal')->after('sent_date');
            $table->text('keterangan')->nullable()->after('perihal');
            
            // Menghapus field yang tidak diperlukan
            $table->dropForeign(['recipient_letter_id']);
            $table->dropForeign(['category_letter_id']);
            $table->dropColumn([
                'recipient_letter_id',
                'category_letter_id', 
                'to_department',
                'recipient_name'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters_out', function (Blueprint $table) {
            // Mengembalikan field yang dihapus
            $table->unsignedBigInteger('recipient_letter_id')->nullable()->after('letter_id');
            $table->unsignedBigInteger('category_letter_id')->after('recipient_letter_id');
            $table->string('to_department')->after('sent_date');
            $table->string('recipient_name')->after('to_department');
            
            // Menghapus field baru
            $table->dropColumn(['perihal', 'keterangan']);
            
            // Mengembalikan foreign key constraints
            $table->foreign('recipient_letter_id')->references('id')->on('sender_letters')->onDelete('set null');
            $table->foreign('category_letter_id')->references('id')->on('category_letters')->onDelete('cascade');
        });
    }
};