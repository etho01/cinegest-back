<?php

namespace App\Models\Movie;

use App\Models\Cinema\Settings\Option;
use Illuminate\Database\Eloquent\Model;

class MovieVersion extends Model
{
    protected $fillable = [
        'versionName',
        'movieId',
        'size',
    ];

    public function movie()
    {
        return $this->belongsTo(\App\Models\Movie::class, 'movieId');
    }

    public function options()
    {
        return $this->belongsToMany(Option::class, 'movie_version_options', 'movieVersionId', 'movieOptionId');
    }
}
