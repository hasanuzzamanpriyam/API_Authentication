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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->double('price')->nullable();
            $table->string('image')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('rating')->nullable();
            $table->string('count_in_stock')->nullable();
            $table->string('num_reviews')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
