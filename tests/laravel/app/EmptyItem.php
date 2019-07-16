<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EmptyItem extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'id',
        'title',
    ];

    public function toSearchableArray()
    {
        return [];
    }
}
