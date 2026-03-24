<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('branch_id', 5);
            $table->enum('gender', ['M', 'F']);
            $table->string('status', 4);
            $table->string('job_type', 10);
            $table->date('join_date')->nullable();
            $table->date('quit_date')->nullable();
            $table->string('home_address', 250)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
