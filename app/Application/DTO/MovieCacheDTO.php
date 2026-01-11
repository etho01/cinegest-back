<?php

namespace App\Application\DTO;

final class MovieCacheDTO
{
    public string $id;
    public string $externalId;
    public string $title;
    public ?string $overview;
    public ?string $posterUrl;
    public ?string $backdropUrl;
    public ?float $voteAverage;
    public ?string $releaseDate;
    public ?int $runtime;
    public ?string $director;
    public array $actors;
    public array $genres;

    public function __construct(
        string $id,
        string $externalId,
        string $title,
        ?string $overview = null,
        ?string $posterUrl = null,
        ?string $backdropUrl = null,
        ?float $voteAverage = null,
        ?string $releaseDate = null,
        ?int $runtime = null,
        ?string $director = null,
        array $actors = [],
        array $genres = []
    ) {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->title = $title;
        $this->overview = $overview;
        $this->posterUrl = $posterUrl;
        $this->backdropUrl = $backdropUrl;
        $this->voteAverage = $voteAverage;
        $this->releaseDate = $releaseDate;
        $this->runtime = $runtime;
        $this->director = $director;
        $this->actors = $actors;
        $this->genres = $genres;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'externalId' => $this->externalId,
            'title' => $this->title,
            'overview' => $this->overview,
            'posterUrl' => $this->posterUrl,
            'backdropUrl' => $this->backdropUrl,
            'voteAverage' => $this->voteAverage,
            'releaseDate' => $this->releaseDate,
            'runtime' => $this->runtime,
            'director' => $this->director,
            'actors' => $this->actors,
            'genres' => $this->genres,
        ];
    }
}
