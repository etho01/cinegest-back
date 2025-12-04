<?php

namespace App\Http\Resources\Api\App\Entity\Cinema;

use App\Http\Resources\Api\App\Entity\Cinema\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieVersionResource extends JsonResource
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
            'movieId' => $this->movieId,
            'versionName' => $this->versionName,
            'size' => $this->size,
            'movie' => new MovieResource($this->whenLoaded('movie')),
        ];
    }
}
