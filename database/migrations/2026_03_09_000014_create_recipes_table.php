<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 5);
            $table->string('item_type', 5);
            $table->string('item_code', 10);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 10);
            $table->integer('purchase_price');
            $table->timestamps();

            $table->unique(['product_id', 'item_type', 'item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
