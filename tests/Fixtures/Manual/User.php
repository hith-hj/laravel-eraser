<?php

declare(strict_types=1);

namespace Eraser\Tests\Fixtures\Manual;

use Eraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends Model
{
    use HasManualEraser;

    public $timestamps = false;

    public array $erasable = ['posts'];

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
