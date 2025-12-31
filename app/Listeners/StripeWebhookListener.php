<?php

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;
use App\Models\User;
use App\Mail\BookingConfirmation;

class StripeWebhookListener
{
    /**
     * Handle the Stripe webhook event.
     */
    public function handle(WebhookReceived $event): void
    {
        // Handle payment_intent.succeeded event
        if ($event->payload['type'] === 'payment_intent.succeeded') {
            $paymentIntent = $event->payload['data']['object'];
            
            // Extract metadata
            $metadata = $paymentIntent['metadata'] ?? [];
            $bookingId = $metadata['bookingId'] ?? null;
            
            Log::info('Payment confirmed', [
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => $paymentIntent['amount'] / 100, // Convert from cents
                'currency' => $paymentIntent['currency'],
                'booking_id' => $bookingId,
                'metadata' => $metadata,
            ]);
            
            // Update existing booking
            if ($bookingId) {
                try {
                    $booking = Booking::find($bookingId);
                    
                    if ($booking) {
                        // Mark booking as paid
                        $booking->markAsPaid();
                        
                        Log::info('Booking marked as paid', [
                            'booking_id' => $booking->id,
                            'total_tickets' => $booking->total_tickets,
                            'session_id' => $booking->session_id,
                        ]);
                        
                        // Load relationships for email
                        $booking->load(['user', 'session.cinema', 'session.movie', 'session.room', 'items']);
                        
                        // Send confirmation email to customer
                        try {
                            Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
                            
                            Log::info('Confirmation email sent', [
                                'booking_id' => $booking->id,
                                'email' => $booking->user->email,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send confirmation email', [
                                'booking_id' => $booking->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                        
                    } else {
                        Log::warning('Booking not found for payment intent', [
                            'booking_id' => $bookingId,
                            'payment_intent_id' => $paymentIntent['id'],
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to update booking', [
                        'error' => $e->getMessage(),
                        'booking_id' => $bookingId,
                        'payment_intent_id' => $paymentIntent['id'],
                    ]);
                }
            }
        }
        
        // Handle payment_intent.payment_failed event
        if ($event->payload['type'] === 'payment_intent.payment_failed') {
            $paymentIntent = $event->payload['data']['object'];
            
            Log::error('Payment failed', [
                'payment_intent_id' => $paymentIntent['id'],
                'error' => $paymentIntent['last_payment_error'] ?? null,
                'metadata' => $paymentIntent['metadata'] ?? [],
            ]);
            
            // Mark booking as failed if it exists
            $booking = Booking::where('payment_intent_id', $paymentIntent['id'])->first();
            if ($booking) {
                $booking->markAsCancelled();
            }
        }
    }
}
