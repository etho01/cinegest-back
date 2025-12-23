<?php

namespace App\Models\Cinema\Settings;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'name',
        'options_type_id',
        'price',
    ];

    public function type()
    {
        return $this->belongsTo(OptionsType::class, 'options_type_id');
    }
}
