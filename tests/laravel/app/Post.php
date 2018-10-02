<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'body',
    ];
}
