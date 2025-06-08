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
        // Add deleted_at column to goods_in table if not exists
        if (Schema::hasTable('goods_in') && !Schema::hasColumn('goods_in', 'deleted_at')) {
            Schema::table('goods_in', function (Blueprint $table) {
                $table->softDeletes(); // This adds deleted_at column
            });
        }

        // Add deleted_at column to items table if not exists
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'deleted_at')) {
            Schema::table('items', function (Blueprint $table) {
                $table->softDeletes(); // This adds deleted_at column
            });
        }

        // Ensure goods_in table has correct structure
        if (!Schema::hasTable('goods_in')) {
            Schema::create('goods_in', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('supplier_id');
                $table->integer('quantity')->default(0);
                $table->date('date_received');
                $table->string('invoice_number');
                $table->timestamps();
                $table->softDeletes(); // deleted_at column

                // Foreign keys
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');

                // Indexes
                $table->index(['item_id', 'date_received']);
                $table->index('supplier_id');
                $table->index('invoice_number');
            });
        }

        // Ensure items table has correct structure  
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('unit_id');
                $table->unsignedBigInteger('brand_id');
                $table->integer('quantity')->default(0);
                $table->string('image')->nullable();
                $table->enum('active', ['true', 'false'])->default('true');
                $table->timestamps();
                $table->softDeletes(); // deleted_at column

                // Foreign keys
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');

                // Indexes
                $table->index('code');
                $table->index(['category_id', 'active']);
                $table->index('active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes if needed
        if (Schema::hasColumn('goods_in', 'deleted_at')) {
            Schema::table('goods_in', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('items', 'deleted_at')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};