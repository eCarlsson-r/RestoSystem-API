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
        Schema::create('buffets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'All You Can Eat Standard'
            $table->decimal('price_adult', 12, 2);
            $table->decimal('price_child', 12, 2);
            $table->integer('duration_minutes')->default(90);
            $table->boolean('is_active')->default(true);
            $table->string('description', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buffets');
    }
};
