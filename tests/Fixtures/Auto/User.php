<?php

declare(strict_types=1);

namespace Eraser\Tests\Fixtures\Auto;

use Eraser\Traits\HasAutoEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends Model
{
    use HasAutoEraser;

    public $timestamps = false;

    protected $table = 'users';

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
