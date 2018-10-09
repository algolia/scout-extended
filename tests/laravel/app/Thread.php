<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

final class Thread extends Model
{
    use Searchable;

    protected $fillable = [
        'body',
    ];
}
