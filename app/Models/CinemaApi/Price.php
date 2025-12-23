<?php

namespace App\Models\CinemaApi;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'name',
        'description',
        'currency',
        'amount',
        'cinema_api_id',
    ];
}
