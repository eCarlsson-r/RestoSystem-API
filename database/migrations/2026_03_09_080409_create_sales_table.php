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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('branch_id', 5);
            $table->string('table_number', 11);
            $table->integer('floor_number');
            $table->foreignId('employee_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('date');
            $table->time('time');
            $table->tinyInteger('discount')->default(0);
            $table->tinyInteger('tax')->default(0);
            $table->string('status', 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
