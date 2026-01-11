<?php

namespace App\Http\Resources\Api\App\Entity\Cinema;

use App\Http\Resources\Api\App\Entity\Cinema\Settings\RoomResource;
use App\Models\Cinema\Key;
use App\Models\Cinema\StorageItem;
use App\Repository\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $bookingRepository = new BookingRepository();

        return [
            'id' => $this->id,
            'movieVersion' => new MovieVersionResource($this->whenLoaded('movieVersion')),
            'room' => new RoomResource($this->whenLoaded('room')),
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'status' => $this->status,
            'statusKey' => $this->statusKey(),
            'statusServer' => $this->getStatusServer(),
            "nbSeatsSold" => $bookingRepository->getTotalTicketsSold($this->id),
        ];
    }
    
    public function statusKey()
    {
        return Key::where('dateStart', '<=', $this->startTime)
            ->where('dateEnd', '>=', $this->startTime)
            ->where('cinemaId', $this->cinemaId)
            ->where('roomId', $this->roomId)
            ->where('movieVersionId', $this->movieVersionId)
            ->exists() ? 'hasKey' : 'noKey';
    }

    public function getStatusServer() {
        return StorageItem::where('roomId', $this->roomId)
            ->where('movieVersionId', $this->movieVersionId)
            ->exists() ? 'hasMovieServer' : 'noMovieServer';
    }
}
