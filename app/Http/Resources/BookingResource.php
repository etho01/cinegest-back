<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'totalAmount' => $this->total_amount,
            'currency' => $this->currency,
            'totalTickets' => $this->total_tickets,
            'paidAt' => $this->paid_at?->format('Y-m-d H:i:s'),
            'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
            'session' => [
                'id' => $this->session->id,
                'date' => $this->session->startTime,
                'time' => $this->session->startTime->format('H:i'),
                'movie' => [
                    'id' => $this->session->movie->id,
                    'title' => $this->session->movie->title,
                    'posterUrl' => $this->session->movie->cache->posterUrl,
                ],
                'cinema' => [
                    'id' => $this->session->cinema->id,
                    'name' => $this->session->cinema->name,
                ],
                'room' => [
                    'id' => $this->session->room->id,
                    'name' => $this->session->room->name,
                ],
            ],
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'priceName' => $item->price_name,
                    'priceAmount' => $item->price_amount,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ];
            }),
        ];
    }
}
