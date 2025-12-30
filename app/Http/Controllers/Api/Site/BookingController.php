<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingPaymentIntentRequest;
use App\Http\Resources\PaymentIntentResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Models\User;

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
        try {
            // Validated data
            $validated = $request->validated();

            // Get the user
            $user = User::findOrFail($validated['userId']);

            // Convert amount to cents (Stripe expects smallest currency unit)
            $amountInCents = (int) ($validated['totalAmount'] * 100);

            // Create payment intent via Laravel Cashier
            $payment = $user->pay($amountInCents, [
                'metadata' => [
                    'sessionId' => $validated['sessionId'],
                    'userId' => $validated['userId'],
                    'items' => json_encode($validated['items']),
                    'totalAmount' => $validated['totalAmount'],
                ],
            ]);

            // Prepare response data
            $paymentIntentData = [
                'clientSecret' => $payment->client_secret,
                'paymentIntentId' => $payment->id,
            ];

            return response()->json(
                new PaymentIntentResource($paymentIntentData)
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Payment intent creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
