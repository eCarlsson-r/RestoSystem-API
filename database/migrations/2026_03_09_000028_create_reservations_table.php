<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            
            // Timing
            $table->date('event_date');
            $table->time('event_time');
            
            // Links
            $table->foreignId('buffet_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onUpdate('cascade')->onDelete('cascade'); // Important for multi-branch
            $table->foreignId('employee_id')->constrained()->onUpdate('cascade')->onDelete('cascade'); // The staff who took the booking
            
            // Financials & Counts
            $table->integer('guaranteed_pax')->default(1);
            $table->decimal('deposit_amount', 14, 2)->default(0);
            $table->enum('deposit_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            
            // Status Management
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            
            // Link to the final sale once they arrive
            $table->unsignedBigInteger('sale_id')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->constrained();
            $table->timestamp('buffet_start_at')->nullable();
            $table->timestamp('buffet_end_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
