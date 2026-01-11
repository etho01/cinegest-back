<?php

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use App\UseCase\Site\Booking\ConfirmBookingPayment;
use App\UseCase\Site\Booking\CancelBooking;

class StripeWebhookListener
{
    public function __construct(
        private ConfirmBookingPayment $confirmBookingPayment,
        private CancelBooking $cancelBooking
    ) {}

    /**
     * Handle the Stripe webhook event.
     */
    public function handle(WebhookReceived $event): void
    {
        // Handle payment_intent.succeeded event
        if ($event->payload['type'] === 'payment_intent.succeeded') {
            $this->handlePaymentSucceeded($event->payload);
        }
        
        // Handle payment_intent.payment_failed event
        if ($event->payload['type'] === 'payment_intent.payment_failed') {
            $this->handlePaymentFailed($event->payload);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];
        $metadata = $paymentIntent['metadata'] ?? [];
        $bookingId = $metadata['bookingId'] ?? null;

        Log::info('Payment confirmed', [
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'] / 100,
            'currency' => $paymentIntent['currency'],
            'booking_id' => $bookingId,
            'metadata' => $metadata,
        ]);

        if ($bookingId) {
            $this->confirmBookingPayment->handle((int) $bookingId);
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];
        $metadata = $paymentIntent['metadata'] ?? [];
        $bookingId = $metadata['bookingId'] ?? null;

        Log::error('Payment failed', [
            'payment_intent_id' => $paymentIntent['id'],
            'error' => $paymentIntent['last_payment_error'] ?? null,
            'booking_id' => $bookingId,
            'metadata' => $metadata,
        ]);

        if ($bookingId) {
            $this->cancelBooking->handle((int) $bookingId);
        }
    }
}
