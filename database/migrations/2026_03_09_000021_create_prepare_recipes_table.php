<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prepare_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('prepare_id', 5);
            $table->string('ingredient_id', 5);
            $table->integer('qty');
            $table->integer('purchase_price');
            $table->timestamps();

            $table->unique(['prepare_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepare_recipes');
    }
};
