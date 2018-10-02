<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

final class Thread extends Model
{
    protected $fillable = [
        'body',
    ];
}
