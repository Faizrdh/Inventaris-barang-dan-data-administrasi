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
        Schema::create('letters_out', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('letter_id');
            $table->unsignedBigInteger('recipient_letter_id')->nullable();
            $table->unsignedBigInteger('category_letter_id');
            $table->unsignedBigInteger('user_id');
            $table->date('sent_date');
            $table->string('to_department');
            $table->string('recipient_name');
            $table->text('notes')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('letter_id')->references('id')->on('letters')->onDelete('cascade');
            $table->foreign('recipient_letter_id')->references('id')->on('sender_letters')->onDelete('set null');
            $table->foreign('category_letter_id')->references('id')->on('category_letters')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['sent_date']);
            $table->index(['letter_id']);
            $table->index(['recipient_letter_id']);
            $table->index(['category_letter_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters_out');
    }
};