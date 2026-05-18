<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\DailySalesReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateDailySalesReportJobV2 implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = now()->toDateString();
        $totalRevenue = 0;
        $totalBookings = 0;
        $successfulPayments = 0;
        $failedPayments = 0;

        Log::info('Daily sales report generation started');

        Booking::chunkById(1000, function ($bookings)
        use (
            &$totalRevenue,
            &$totalBookings,
            &$successfulPayments,
            &$failedPayments
        ) {
            Log::info('Processing booking chunk', [
                'chunk_size' => $bookings->count(),
            ]);

            foreach ($bookings as $booking) {
                $totalBookings++;

                if ($booking->payment_status === PaymentStatus::Paid) {
                    $successfulPayments++;
                    $totalRevenue += $booking->eventSeat->event->price;
                }

                if ($booking->payment_status === PaymentStatus::Failed)
                    $failedPayments++;
            }
        });

        DailySalesReport::updateOrCreate(
            [
                'report_date' => $date,
            ],
            [
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'successful_payments' => $successfulPayments,
                'failed_payments' => $failedPayments,
            ]
        );

        Log::info('Daily sales report generated successfully', [
            'date' => $date,
            'total_revenue' => $totalRevenue,
        ]);
    }
}
