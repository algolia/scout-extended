<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

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

    public function threads()
    {
        return $this->hasMany('App\Thread');
    }

    public function toSearchableArray()
    {
        return array_merge($this->toArray(), [
            'views_count' => '100',
            'category_type' => 'published',
        ]);
    }
}
