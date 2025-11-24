<?php

declare(strict_types=1);

namespace Eraser\Tests\Fixtures\Manual;

use Eraser\Traits\HasManualEraser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Post extends Model
{
    use HasManualEraser;

    public $timestamps = false;

    public array $erasable = ['comments'];

    protected $guarded = [];

    protected $table = 'posts';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
