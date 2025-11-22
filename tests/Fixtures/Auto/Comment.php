<?php

namespace Hith\LaravelEraser\Tests\Fixtures\Auto;

use Hith\LaravelEraser\Traits\HasAutoEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasAutoEraser;

    protected $table = 'comments';

    public $timestamps = false;

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
