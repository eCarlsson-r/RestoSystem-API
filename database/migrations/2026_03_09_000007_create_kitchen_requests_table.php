<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_requests', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->string('from_branch_id', 5);
            $table->string('from_storage', 4);
            $table->string('to_branch_id', 5);
            $table->string('to_storage', 4);
            $table->date('respond_date')->nullable();
            $table->time('respond_time')->nullable();
            $table->string('status', 5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_requests');
    }
};
