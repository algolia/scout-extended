<?php

namespace Modules\Taxonomy;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
    ];
}
