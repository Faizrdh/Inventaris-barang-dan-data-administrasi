<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('letters_in', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('letter_id');
            $table->unsignedBigInteger('sender_letter_id');
            $table->unsignedBigInteger('category_letter_id');
            $table->unsignedBigInteger('user_id');
            $table->date('received_date');
            $table->string('letter_number');
            $table->text('subject');
            $table->text('notes')->nullable();
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['pending', 'processed', 'completed', 'rejected']);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('letter_id')->references('id')->on('letters')->onDelete('cascade');
            $table->foreign('sender_letter_id')->references('id')->on('sender_letters')->onDelete('cascade');
            $table->foreign('category_letter_id')->references('id')->on('category_letters')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes untuk performa
            $table->index(['received_date', 'status']);
            $table->index(['priority', 'status']);
            $table->index('letter_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letters_in');
    }
};