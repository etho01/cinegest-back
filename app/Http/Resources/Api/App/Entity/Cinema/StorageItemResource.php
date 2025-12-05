<?php

namespace App\Http\Resources\Api\App\Entity\Cinema;

use App\Http\Resources\Api\App\Entity\Cinema\Settings\RoomResource;
use App\Http\Resources\Api\App\Entity\Cinema\Settings\StorageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorageItemResource extends JsonResource
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
            'status' => $this->status,
            'storage' => new StorageResource($this->whenLoaded('storage')),
            'room' => new RoomResource($this->whenLoaded('room')),
            'movieVersion' => new MovieVersionResource($this->whenLoaded('movieVersion')),
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'origin' => new StorageResource($this->whenLoaded('origin')),
        ];
    }
}
