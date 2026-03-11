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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number', 11);
            $table->string('floor_number', 11);
            $table->string('branch_id', 5);
            $table->integer('capacity');
            $table->integer('size')->default(1);
            $table->string('direction', 1)->default('H');
            $table->enum('status', ['available', 'occupied', 'reserved', 'dirty'])->default('available');
            $table->integer('position_x')->default(0); // For custom layout
            $table->integer('position_y')->default(0); // For custom layout
            $table->enum('shape', ['circle', 'square'])->default('square');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
