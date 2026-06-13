<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public string $type // new_booking, payment_received, confirmed, rejected, cancelled, reminder
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $restaurantName = $this->booking->restaurant->name;
        $dateStr = $this->booking->booking_date->format('M d, Y');
        $timeStr = \Carbon\Carbon::parse($this->booking->booking_time)->format('h:i A');

        $mail = (new MailMessage)
            ->subject(config('app.name', 'Zayka Dining') . " - Booking Update for {$restaurantName}");

        switch ($this->type) {
            case 'new_booking':
                $mail->line("A new table booking has been requested at your restaurant.")
                    ->line("Details:")
                    ->line("- Guest Count: {$this->booking->guest_count}")
                    ->line("- Date: {$dateStr}")
                    ->line("- Time: {$timeStr}")
                    ->action('View Booking Dashboard', route('dashboard'));
                break;
            case 'payment_received':
                $mail->line("We have received your payment of ₹{$this->booking->payment_amount} for the reservation at {$restaurantName}.")
                    ->line("Your booking is currently pending confirmation from the restaurant.")
                    ->action('View Your Dashboard', route('dashboard'));

                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $pdf = $invoiceService->generatePdf($this->booking);
                    $mail->attachData($pdf->output(), "invoice-{$this->booking->id}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
                } catch (\Exception $e) {
                    logger()->error('Failed to attach invoice PDF: ' . $e->getMessage());
                }
                break;
            case 'confirmed':
                $mail->line("Good news! Your table booking at {$restaurantName} has been confirmed.")
                    ->line("Details:")
                    ->line("- Date: {$dateStr}")
                    ->line("- Time: {$timeStr}")
                    ->line("- Table: " . ($this->booking->table ? $this->booking->table->name : 'Assigned'))
                    ->action('View Your Booking', route('dashboard'));

                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $pdf = $invoiceService->generatePdf($this->booking);
                    $mail->attachData($pdf->output(), "invoice-{$this->booking->id}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
                } catch (\Exception $e) {
                    logger()->error('Failed to attach invoice PDF: ' . $e->getMessage());
                }
                break;
            case 'rejected':
                $mail->line("Unfortunately, your booking request at {$restaurantName} has been rejected.")
                    ->line("If you made a payment, it will be refunded.")
                    ->action('View Alternatives', route('dashboard'));
                break;
            case 'cancelled':
                $mail->line("Your booking at {$restaurantName} has been cancelled.")
                    ->action('Book Another Table', route('dashboard'));
                break;
            case 'reminder':
                $mail->line("Friendly reminder: You have an upcoming table reservation at {$restaurantName} tomorrow.")
                    ->line("Time: {$timeStr}")
                    ->action('View Booking Details', route('dashboard'));
                break;
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'restaurant_name' => $this->booking->restaurant->name,
            'type' => $this->type,
            'message' => $this->getDatabaseMessage(),
        ];
    }

    private function getDatabaseMessage(): string
    {
        $restaurantName = $this->booking->restaurant->name;
        $dateStr = $this->booking->booking_date->format('M d, Y');
        $timeStr = \Carbon\Carbon::parse($this->booking->booking_time)->format('h:i A');

        return match ($this->type) {
            'new_booking' => "New reservation request for {$this->booking->guest_count} guests on {$dateStr} at {$timeStr}.",
            'payment_received' => "Payment of ₹{$this->booking->payment_amount} received for {$restaurantName}.",
            'confirmed' => "Reservation at {$restaurantName} on {$dateStr} @ {$timeStr} is CONFIRMED.",
            'rejected' => "Reservation at {$restaurantName} was rejected.",
            'cancelled' => "Reservation at {$restaurantName} was cancelled.",
            'reminder' => "Upcoming reservation at {$restaurantName} tomorrow at {$timeStr}.",
            default => "Booking status updated."
        };
    }
}
