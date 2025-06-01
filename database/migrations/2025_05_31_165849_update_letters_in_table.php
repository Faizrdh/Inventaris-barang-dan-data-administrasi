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
            // Hapus kolom yang tidak diperlukan
            $table->dropColumn(['letter_number', 'subject', 'priority', 'status']);
            
            // Tambah kolom baru
            $table->string('from_department')->nullable()->after('category_letter_id');
            $table->string('sender_name')->nullable()->after('from_department');
            
            // Hapus index yang tidak diperlukan
            $table->dropIndex(['priority', 'status']);
            $table->dropIndex('letters_in_letter_number_index');
            
            // Tambah index baru
            $table->index('from_department');
            $table->index('sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters_in', function (Blueprint $table) {
            // Kembalikan kolom yang dihapus
            $table->string('letter_number')->after('received_date');
            $table->text('subject')->after('letter_number');
            $table->enum('priority', ['low', 'medium', 'high'])->after('notes');
            $table->enum('status', ['pending', 'processed', 'completed', 'rejected'])->after('priority');
            
            // Hapus kolom baru
            $table->dropColumn(['from_department', 'sender_name']);
            
            // Kembalikan index
            $table->index(['priority', 'status']);
            $table->index('letter_number');
            
            // Hapus index baru
            $table->dropIndex(['from_department']);
            $table->dropIndex(['sender_name']);
        });
    }
};