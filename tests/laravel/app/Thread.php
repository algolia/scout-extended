<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Thread extends Model
{
    use Searchable;

    protected $fillable = [
        'body',
        'slug',
        'description_at_the_letter',
    ];
}
