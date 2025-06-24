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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // kode
            $table->string('name'); // nama
            $table->string('employee_id'); // nip
            $table->date('application_date'); // tanggal_pengajuan
            $table->string('leave_type'); // jenis_cuti
            $table->date('start_date'); // tanggal_mulai
            $table->date('end_date'); // tanggal_selesai
            $table->integer('total_days'); // total_hari
            $table->text('description'); // keterangan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('document_path')->nullable(); // dokumen
            $table->unsignedBigInteger('user_id'); // user yang mengajukan
            $table->unsignedBigInteger('approved_by')->nullable(); // user yang approve
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['status', 'application_date']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};