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
        // Menentukan koneksi yang akan digunakan, yaitu db_inventaris
        Schema::connection('db_inventaris')->create('cutis', function (Blueprint $table) {
            $table->id(); // Kolom ID utama
            $table->string('kode')->unique(); // Kode unik untuk setiap pengajuan cuti
            $table->string('nama'); // Nama pengaju cuti
            $table->string('nip'); // Nomor Induk Pegawai (NIP)
            $table->string('file_izin')->nullable(); // Path ke file PDF dokumen izin, nullable
            $table->date('tanggal_pengajuan'); // Tanggal pengajuan cuti
            $table->date('tanggal_mulai'); // Tanggal mulai cuti
            $table->date('tanggal_selesai'); // Tanggal selesai cuti
            $table->integer('total_hari'); // Total hari cuti
            $table->text('keterangan'); // Keterangan pengajuan cuti
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status pengajuan cuti
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Foreign key ke tabel users untuk mengetahui siapa yang menyetujui cuti
            $table->timestamp('approved_at')->nullable(); // Waktu persetujuan
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perbaikan: Gunakan nama tabel 'cutis' untuk drop, bukan 'leave_applications'
        Schema::connection('db_inventaris')->dropIfExists('cutis');
    }
};