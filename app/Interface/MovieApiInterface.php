<?php

namespace App\Interface;

interface MovieApiInterface
{
    public static function search(string $searchTerm): array;
}