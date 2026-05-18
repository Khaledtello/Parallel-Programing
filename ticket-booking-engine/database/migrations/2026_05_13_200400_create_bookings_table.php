<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
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

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('event_seat_id')
                ->constrained('event_seat')
                ->cascadeOnDelete();

            $table->enum('status', array_column(BookingStatus::cases(), 'value'))
                ->default(BookingStatus::Pending);

            $table->enum('payment_status', array_column(PaymentStatus::cases(), 'value'))
                ->default(PaymentStatus::Pending);

            $table->timestamp('booked_at')->nullable();
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
