<?php

namespace App\Models\Entity;

use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    protected $fillable = [
        'name',
        'address',
        'address_complement',
        'postal_code',
        'city',
        'country',
        'entity_id',
    ];
}
