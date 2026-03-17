<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('item_type', 50);
            $table->string('item_code', 10);
            $table->integer('quantity');
            $table->integer('item_price');
            $table->tinyInteger('discount_pcnt')->default(0);
            $table->integer('discount_amnt')->default(0);
            $table->string('item_note', 500)->nullable();
            $table->string('item_status', 1)->default('O');
            $table->string('order_employee', 5)->nullable();
            $table->date('order_date')->nullable();
            $table->time('order_time')->nullable();
            $table->string('deliver_employee', 5)->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_records');
    }
};
