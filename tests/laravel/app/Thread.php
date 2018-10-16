<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use Searchable;

    protected $fillable = [
        'body',
    ];
}
