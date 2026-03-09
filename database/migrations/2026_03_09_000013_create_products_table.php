<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('code', 5)->primary();
            $table->string('name', 100);
            $table->string('desc', 200)->nullable();
            $table->integer('img_no')->default(0);
            $table->string('category_id', 2);
            $table->integer('price');
            $table->integer('cost');
            $table->tinyInteger('discount')->default(0);
            $table->tinyInteger('soldout')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
