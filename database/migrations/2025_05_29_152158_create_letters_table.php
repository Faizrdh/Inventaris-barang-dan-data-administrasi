<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('category_letter_id');
            $table->string('file_name')->nullable(); 
            $table->string('file_path')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();
            
            $table->foreign('category_letter_id')->references('id')->on('category_letters')->onDelete('cascade');
            $table->index(['code', 'category_letter_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('letters');
    }
};