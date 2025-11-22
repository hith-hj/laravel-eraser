<?php

namespace Hith\LaravelEraser\Tests\Fixtures\Manual;

use Hith\LaravelEraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasManualEraser;

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
