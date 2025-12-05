<?php

namespace App\Http\Resources\Api\App\Entity\Cinema;

use App\Http\Resources\Api\App\Entity\Cinema\Settings\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cinemaId' => $this->cinemaId,
            'roomId' => $this->roomId,
            'movieVersionId' => $this->movieVersionId,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'movieVersion' => new MovieVersionResource($this->whenLoaded('movieVersion')),
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
