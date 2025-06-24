<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sender_letters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('from_department');
            $table->text('destination');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sender_letters');
    }
};