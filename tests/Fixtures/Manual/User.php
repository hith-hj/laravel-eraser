<?php

namespace Hith\LaravelEraser\Tests\Fixtures\Manual;

use Hith\LaravelEraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasManualEraser;

    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = [];

    public array $eraserRelationsToDelete = ['posts'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
