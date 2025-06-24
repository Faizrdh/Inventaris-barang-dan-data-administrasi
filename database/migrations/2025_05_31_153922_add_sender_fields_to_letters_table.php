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
        Schema::table('letters', function (Blueprint $table) {
            // Menambahkan kolom sender_letter_id setelah category_letter_id
            $table->unsignedBigInteger('sender_letter_id')->nullable()->after('category_letter_id');
            
            // Menambahkan kolom from_department setelah sender_letter_id
            $table->string('from_department')->nullable()->after('sender_letter_id');
            
            // Menambahkan foreign key constraint untuk sender_letter_id
            $table->foreign('sender_letter_id')
                  ->references('id')
                  ->on('sender_letters')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            // Menambahkan index untuk performa query
            $table->index('sender_letter_id');
            $table->index('from_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['sender_letter_id']);
            
            // Hapus index
            $table->dropIndex(['sender_letter_id']);
            $table->dropIndex(['from_department']);
            
            // Hapus kolom
            $table->dropColumn(['sender_letter_id', 'from_department']);
        });
    }
};