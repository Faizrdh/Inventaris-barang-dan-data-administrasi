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
        Schema::create('item_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrower_id');
            $table->string('item_code');
            $table->date('return_date');
            $table->enum('status', ['Baik', 'Rusak']);
            $table->timestamps();
            
            // Foreign key constraints yang benar
            $table->foreign('borrower_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('item_code')->references('code')->on('items')->onDelete('cascade');
            
            // Index untuk performa
            $table->index(['borrower_id', 'item_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_returns');
    }
};