<?php

namespace App\Interface;

interface MovieApiInterface
{
    public static function search(string $searchTerm): array;

    public static function getDetails(string $externalId): ?array;
}