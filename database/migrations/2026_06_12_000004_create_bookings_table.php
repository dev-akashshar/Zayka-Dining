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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->integer('guest_count');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled, rejected
            $table->string('payment_status')->default('unpaid'); // unpaid, paid_advance, paid_full
            $table->string('payment_type')->default('none'); // none, advance, full
            $table->decimal('payment_amount', 10, 2)->default(0.00);
            $table->string('stripe_payment_intent_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
