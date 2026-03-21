<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onUpdate('cascade')->onDelete('cascade');
            $table->string('pay_method', 2)->nullable();
            $table->string('pay_bank', 50)->nullable();
            $table->string('pay_card', 20)->nullable();
            $table->integer('pay_amount');
            $table->integer('pay_change')->default(0);
            $table->string('card_type', 10)->nullable();
            $table->string('voucher', 10)->nullable();
            $table->foreignId('employee_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
