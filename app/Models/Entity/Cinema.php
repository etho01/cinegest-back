<?php

namespace App\Models\Entity;

use App\Models\Cinema\Settings\Option;
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

    public function options()
    {
        return $this->hasMany(Option::class, 'cinema_id', 'id')->where('price', '>', 0);
    }
}
