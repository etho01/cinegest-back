<?php

namespace App\Http\Resources\Api\App\Entity\Cinema;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'releaseDate' => $this->releaseDate,
            'externalId' => $this->externalId,
            'size' => $this->size,
        ];
    }
}
