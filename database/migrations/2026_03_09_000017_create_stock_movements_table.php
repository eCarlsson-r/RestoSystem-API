<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('from_branch_id', 5);
            $table->string('from_storage', 4);
            $table->string('to_branch_id', 5);
            $table->string('to_storage', 4);
            $table->date('date');
            $table->time('time');
            $table->string('status', 5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
