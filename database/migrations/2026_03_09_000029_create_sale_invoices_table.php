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
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('paymethod', 2)->nullable();
            $table->string('paybank', 50)->nullable();
            $table->string('paycard', 20)->nullable();
            $table->integer('payamount');
            $table->integer('paychange')->default(0);
            $table->string('cardtype', 10)->nullable();
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
