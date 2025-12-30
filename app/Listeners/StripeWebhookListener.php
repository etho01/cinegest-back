<?php

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

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
            $sessionId = $metadata['sessionId'] ?? null;
            $userId = $metadata['userId'] ?? null;
            $totalAmount = $metadata['totalAmount'] ?? null;
            
            Log::info('Payment confirmed', [
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => $paymentIntent['amount'] / 100, // Convert from cents
                'currency' => $paymentIntent['currency'],
                'session_id' => $sessionId,
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'metadata' => $metadata,
            ]);
            
            // TODO: Update your booking/order status in database
            // TODO: Send confirmation email to customer
            // TODO: Create ticket records
            
            // Example:
            // if ($sessionId && $userId) {
            //     Booking::where('session_id', $sessionId)
            //         ->where('user_id', $userId)
            //         ->update(['status' => 'paid']);
            //     
            //     // Send confirmation email
            //     $user = User::find($userId);
            //     Mail::to($user->email)->send(new BookingConfirmation($sessionId));
            // }
        }
        
        // Handle payment_intent.payment_failed event
        if ($event->payload['type'] === 'payment_intent.payment_failed') {
            $paymentIntent = $event->payload['data']['object'];
            
            Log::error('Payment failed', [
                'payment_intent_id' => $paymentIntent['id'],
                'error' => $paymentIntent['last_payment_error'] ?? null,
                'metadata' => $paymentIntent['metadata'] ?? [],
            ]);
            
            // TODO: Handle failed payment
            // TODO: Notify user
        }
    }
}
