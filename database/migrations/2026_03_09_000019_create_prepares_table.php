<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prepares', function (Blueprint $table) {
            $table->string('code', 5)->primary();
            $table->string('name', 100);
            $table->integer('cost');
            $table->integer('qty');
            $table->string('unit', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepares');
    }
};
