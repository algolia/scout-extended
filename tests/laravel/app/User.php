<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable
{
    use Notifiable, Searchable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function toSearchableArray()
    {
        return array_merge($this->toArray(), [
            'views_count' => 100,
            'category_type' => 'published'
        ]);
    }
}
