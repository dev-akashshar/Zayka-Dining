<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate PDF binary stream for a booking invoice.
     */
    public function generatePdf(Booking $booking): \Barryvdh\DomPDF\PDF
    {
        // Load relationships to display on the invoice
        $booking->load(['restaurant', 'user', 'table']);

        return Pdf::loadView('emails.invoice', [
            'booking' => $booking,
            'restaurant' => $booking->restaurant,
            'user' => $booking->user,
            'table' => $booking->table,
        ]);
    }
}
