<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

return [

    /*
    |--------------------------------------------------------------------------
    | Eraser: Delete Relations on Model Deletion
    |--------------------------------------------------------------------------
    |
    | Determines whether the "eraser" should automatically start deleting
    | related models when the parent model is being deleted.
    | Default: true
    |
    */
    'eraser_onDeleteStart' => env('eraser_onDeleteStart', true),

    /*
    |--------------------------------------------------------------------------
    | Eraser: Auto Discover Relations
    |--------------------------------------------------------------------------
    |
    | Controls whether relations to be deleted should be automatically
    | discovered when using the Auto Eraser functionality.
    | Default: true
    |
    */
    'eraser_autoDiscover' => env('eraser_autoDiscover', true),

    /*
    |--------------------------------------------------------------------------
    | Eraser: Logging
    |--------------------------------------------------------------------------
    |
    | Enables or disables global logging for eraser operations.
    | Default: true
    |
    */
    'eraser_logging' => env('eraser_logging', true),

    /*
    |--------------------------------------------------------------------------
    | Eraser: Log Channel
    |--------------------------------------------------------------------------
    |
    | Specifies the logging channel where eraser logs should be sent.
    | If null, the default logging channel will be used.
    |
    */
    'eraser_log_channel' => env('log_channel', null),

    /*
    |--------------------------------------------------------------------------
    | Eraser: Base Deny List
    |--------------------------------------------------------------------------
    |
    | A list of reserved keywords that are used to determine whether a
    | relation should NOT be deleted. If a relation method name contains
    | any of these words, it will be excluded from deletion.
    |
    */
    'eraser_base_deny_list' => [
        'get',
        'set',
        'update',
        'create',
        'save',
        'delete',
        'calc',
        'make',
        'send',
        'attach',
        'detach',
    ],

    /*
    |--------------------------------------------------------------------------
    | Eraser: Parent Relations
    |--------------------------------------------------------------------------
    |
    | Defines which relation types are considered "parent" relations.
    | These relations typically indicate ownership (e.g., BelongsTo).
    |
    */
    'eraser_parent_relations' => [
        BelongsTo::class,
        MorphTo::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Eraser: Many-to-Many Relations
    |--------------------------------------------------------------------------
    |
    | Defines which relation types are considered "many-to-many".
    | For these relations, the eraser will invoke the "detach" method
    | to properly clean up pivot table records.
    |
    */
    'eraser_manytomany_relations' => [
        BelongsToMany::class,
        MorphToMany::class,
    ],
];
