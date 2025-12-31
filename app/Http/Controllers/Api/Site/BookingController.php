<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingPaymentIntentRequest;
use App\Http\Resources\PaymentIntentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * Create a payment intent for booking tickets
     *
     * @param BookingPaymentIntentRequest $request
     * @return JsonResponse
     */
    public function paymentIntent(BookingPaymentIntentRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Validated data
            $validated = $request->validated();

            // Get the user
            $user = User::findOrFail($validated['userId']);

            // Calculate total tickets
            $totalTickets = 0;
            foreach ($validated['items'] as $item) {
                $totalTickets += $item['quantity'] ?? 0;
            }

            // Create booking with pending status
            $booking = Booking::create([
                'user_id' => $validated['userId'],
                'session_id' => $validated['sessionId'],
                'payment_intent_id' => '', // Will be updated after payment intent creation
                'status' => 'pending',
                'total_amount' => $validated['totalAmount'],
                'currency' => 'eur',
                'total_tickets' => $totalTickets,
            ]);

            // Create booking items
            foreach ($validated['items'] as $item) {
                $booking->items()->create([
                    'price_id' => $item['priceId'],
                    'price_name' => $item['priceName'],
                    'price_amount' => $item['priceAmount'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['priceAmount'] * $item['quantity'],
                ]);
            }

            // Convert amount to cents (Stripe expects smallest currency unit)
            $amountInCents = (int) ($validated['totalAmount'] * 100);

            // Create payment intent via Laravel Cashier
            $payment = $user->pay($amountInCents, [
                'metadata' => [
                    'bookingId' => $booking->id,
                    'sessionId' => $validated['sessionId'],
                    'userId' => $validated['userId'],
                    'totalAmount' => $validated['totalAmount'],
                ],
            ]);

            // Update booking with payment intent ID
            $booking->update([
                'payment_intent_id' => $payment->id,
            ]);

            DB::commit();

            // Prepare response data
            $paymentIntentData = [
                'clientSecret' => $payment->client_secret,
                'paymentIntentId' => $payment->id,
            ];

            return response()->json(
                new PaymentIntentResource($paymentIntentData)
            );
        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Payment intent creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
