<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500);
            $table->string('description', 1000)->nullable();
            $table->string('unit', 5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilities');
    }
};
