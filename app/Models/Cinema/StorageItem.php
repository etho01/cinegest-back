<?php

namespace App\Models\Cinema;

use Illuminate\Database\Eloquent\Model;

class StorageItem extends Model
{
    protected $fillable = [
        'roomId',
        'storageId',
        'originId',
        'movieVersionId',
        'movieId',
    ];
}
