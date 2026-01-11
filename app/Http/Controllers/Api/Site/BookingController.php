<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingPaymentIntentRequest;
use App\Http\Resources\PaymentIntentResource;
use App\Http\Resources\BookingResource;
use App\UseCase\Site\Booking\CreateBookingWithPaymentIntent;
use App\UseCase\Site\Booking\GetUserBookings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class BookingController extends Controller
{
    private CreateBookingWithPaymentIntent $createBookingWithPaymentIntent;
    private GetUserBookings $getUserBookings;

    public function __construct(
        CreateBookingWithPaymentIntent $createBookingWithPaymentIntent,
        GetUserBookings $getUserBookings
    ) {
        $this->createBookingWithPaymentIntent = $createBookingWithPaymentIntent;
        $this->getUserBookings = $getUserBookings;
    }

    /**
     * Create a payment intent for booking tickets
     */
    public function paymentIntent(BookingPaymentIntentRequest $request): JsonResponse
    {
        $result = $this->createBookingWithPaymentIntent->handle($request->validated());

        $paymentIntentData = [
            'clientSecret' => $result['payment']->client_secret,
            'paymentIntentId' => $result['payment']->id,
        ];

        return response()->json(
            new PaymentIntentResource($paymentIntentData)
        );
    }

    /**
     * Get authenticated user's bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $status = $request->query('status'); // optional filter: pending, paid, cancelled

            $bookings = $this->getUserBookings->handle($userId, $status);

            return response()->json(BookingResource::collection($bookings));
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve bookings',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
