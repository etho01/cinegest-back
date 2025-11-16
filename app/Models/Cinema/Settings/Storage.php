<?php

namespace App\Models\Cinema\Settings;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = [
        'name',
        'storage_type_id',
        'cinema_id',
        'capacity',
    ];

    public function type()
    {
        return $this->belongsTo(StorageType::class, 'storage_type_id');
    }
}
