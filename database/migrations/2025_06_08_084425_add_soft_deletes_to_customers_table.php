<?php

// SOLUSI 1: Buat migration baru dengan Artisan command
// Jalankan command ini di terminal:
// php artisan make:migration add_soft_deletes_to_customers_table --table=customers

// SOLUSI 2: File migration yang diperbaiki
// Simpan di: database/migrations/2025_06_08_083023_add_soft_deletes_to_customers_table.php

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
        // Cek apakah tabel customers sudah ada
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                // Tambah deleted_at jika belum ada
                if (!Schema::hasColumn('customers', 'deleted_at')) {
                    $table->softDeletes();
                }
                
                // Tambah kolom email jika belum ada
                if (!Schema::hasColumn('customers', 'email')) {
                    $table->string('email')->nullable();
                }
                
                // Tambah kolom active jika belum ada
                if (!Schema::hasColumn('customers', 'active')) {
                    $table->boolean('active')->default(true);
                }
            });
        } else {
            // Jika tabel belum ada, buat tabel baru
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone');
                $table->text('address')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                // Index untuk performa
                $table->index('name');
                $table->index('phone');
                $table->index('active');
                $table->index('deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                // Hapus kolom yang ditambahkan
                if (Schema::hasColumn('customers', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                
                if (Schema::hasColumn('customers', 'email')) {
                    $table->dropColumn('email');
                }
                
                if (Schema::hasColumn('customers', 'active')) {
                    $table->dropColumn('active');
                }
            });
        }
    }
};