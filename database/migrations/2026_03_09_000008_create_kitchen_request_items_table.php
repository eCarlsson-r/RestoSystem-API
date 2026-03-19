<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('kitchen_requests')->onDelete('cascade');
            $table->string('item_type', 4);
            $table->string('item_code', 5);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_request_items');
    }
};
