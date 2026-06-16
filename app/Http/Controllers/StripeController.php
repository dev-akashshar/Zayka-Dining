<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ManagerRequest;
use App\Notifications\BookingStatusNotification;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    /**
     * Redirect to Stripe Checkout Session.
     */
    public function checkout(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);
        $stripeSecret = env('STRIPE_SECRET');

        $amountInCents = (int) ($booking->payment_amount * 100);

        if (empty($stripeSecret) || str_contains($stripeSecret, 'your-')) {
            // Stripe secret not configured, redirect to RestoApp mock checkout page
            return redirect()->route('booking.pay.mock', ['id' => $booking->id]);
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Reservation at '.$booking->restaurant->name,
                            'description' => 'Booking on '.$booking->booking_date->format('Y-m-d').' at '.$booking->booking_time,
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('booking.success', ['id' => $booking->id]).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('dashboard'),
            ]);

            $booking->update(['stripe_payment_intent_id' => $session->id]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            logger()->error('Stripe error: '.$e->getMessage());

            return redirect()->route('dashboard')->with('error', 'Unable to initiate Stripe payment.');
        }
    }

    /**
     * Handle payment success.
     */
    public function success(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        $paymentStatus = $booking->payment_type === 'full' ? 'paid_full' : 'paid_advance';
        $booking->update([
            'payment_status' => $paymentStatus,
            'stripe_payment_intent_id' => $request->query('session_id') ?? 'mock_intent_'.str_random(10),
        ]);

        // Dispatch notifications
        try {
            $booking->user->notify(new BookingStatusNotification($booking, 'payment_received'));
            // Notify managers/staff
            $managers = $booking->restaurant->users()->role('manager')->get();
            foreach ($managers as $manager) {
                $manager->notify(new BookingStatusNotification($booking, 'new_booking'));
            }
        } catch (\Exception $e) {
            logger()->error('Notification failed: '.$e->getMessage());
        }

        return redirect()->route('dashboard')->with('status', 'payment-successful');
    }

    /**
     * Redirect manager request to Stripe Checkout or mock.
     */
    public function managerCheckout(Request $request, int $id)
    {
        $managerRequest = ManagerRequest::findOrFail($id);
        $stripeSecret = env('STRIPE_SECRET');

        $amountInCents = (int) ($managerRequest->payment_amount * 100);

        if (empty($stripeSecret) || str_contains($stripeSecret, 'your-')) {
            // Stripe secret not configured, redirect to mock checkout page
            return redirect()->route('manager-request.pay.mock', ['id' => $managerRequest->id]);
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => 'Manager Registration Fee for '.$managerRequest->restaurant_name,
                            'description' => 'Manager registration request for '.$managerRequest->name,
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('manager-request.success', ['id' => $managerRequest->id]).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('home'),
            ]);

            $managerRequest->update(['stripe_session_id' => $session->id]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            logger()->error('Stripe manager error: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'Unable to initiate payment.');
        }
    }

    /**
     * Confirm paid manager request.
     */
    public function managerSuccess(Request $request, int $id)
    {
        $managerRequest = ManagerRequest::findOrFail($id);
        $managerRequest->update([
            'payment_status' => 'paid',
            'status' => 'pending_approval',
            'stripe_session_id' => $request->query('session_id') ?? 'mock_intent_'.str_random(10),
        ]);

        return redirect()->route('home')->with('status', 'manager-request-submitted');
    }
}
