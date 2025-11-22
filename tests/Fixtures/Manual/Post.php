<?php

namespace Hith\LaravelEraser\Tests\Fixtures\Manual;

use Hith\LaravelEraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasManualEraser;

    protected $guarded = [];

    protected $table = 'posts';

    public $timestamps = false;

    public array $eraserRelationsToDelete = ['comments'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
