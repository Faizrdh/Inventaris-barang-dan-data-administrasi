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
        Schema::table('leave_applications', function (Blueprint $table) {
            // Menambahkan field catatan_validator setelah approved_at
            $table->text('catatan_validator')->nullable()->after('approved_at')->comment('Catatan dari validator/admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Menghapus field catatan_validator jika rollback
            $table->dropColumn('catatan_validator');
        });
    }
};