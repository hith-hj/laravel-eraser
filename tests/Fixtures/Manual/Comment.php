<?php

declare(strict_types=1);

namespace Eraser\Tests\Fixtures\Manual;

use Eraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Comment extends Model
{
    use HasManualEraser;

    public $timestamps = false;

    protected $table = 'comments';

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
