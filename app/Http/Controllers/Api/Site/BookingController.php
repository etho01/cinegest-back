<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingPaymentIntentRequest;
use App\Http\Resources\PaymentIntentResource;
use App\UseCase\Site\Booking\CreateBookingWithPaymentIntent;
use Illuminate\Http\JsonResponse;
use Exception;

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
            $result = CreateBookingWithPaymentIntent::handle($request->validated());

            $paymentIntentData = [
                'clientSecret' => $result['payment']->client_secret,
                'paymentIntentId' => $result['payment']->id,
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
