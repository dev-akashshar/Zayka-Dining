<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function download(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        // Authorization checks
        $isOwner = $user->id === $booking->user_id;
        $isManager = $user->hasRole('manager') && $user->restaurant_id === $booking->restaurant_id;
        $isStaff = $user->hasRole('staff') && $user->restaurant_id === $booking->restaurant_id;
        $isSuperAdmin = $user->hasRole('super_admin');

        if (! ($isOwner || $isManager || $isStaff || $isSuperAdmin)) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoiceService = new InvoiceService();
        $pdf = $invoiceService->generatePdf($booking);

        return $pdf->download("invoice-{$booking->id}.pdf");
    }
}
