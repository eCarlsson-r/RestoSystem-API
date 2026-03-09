<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('branch_id', 5);
            $table->string('storage', 4);
            $table->string('contact_person', 50)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
