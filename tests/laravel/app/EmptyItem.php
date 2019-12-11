<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

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
