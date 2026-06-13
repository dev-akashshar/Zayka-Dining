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
        Schema::create('manager_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('restaurant_name');
            $table->string('restaurant_phone');
            $table->text('restaurant_address');
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->decimal('payment_amount', 10, 2)->default(4999.00);
            $table->string('status')->default('pending_payment'); // pending_payment, pending_approval, approved, rejected
            $table->string('stripe_session_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_requests');
    }
};
