<?php

namespace App\Models\Cinema\Settings;

use Illuminate\Database\Eloquent\Model;

class OptionsType extends Model
{
    protected $fillable = [
        'name',
        'cinema_id',
        'description',
    ];
}
