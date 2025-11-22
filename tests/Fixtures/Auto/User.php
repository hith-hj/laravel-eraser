<?php

namespace Hith\LaravelEraser\Tests\Fixtures\Auto;

use Hith\LaravelEraser\Traits\HasAutoEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasAutoEraser;

    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
