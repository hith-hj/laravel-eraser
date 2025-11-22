
<p align="center">
  <a href="https://github.com/larastan/larastan/actions"><img src="https://github.com/larastan/larastan/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/larastan/larastan/stats"><img src="https://poser.pugx.org/larastan/larastan/d/total.svg" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/larastan/larastan"><img src="https://poser.pugx.org/larastan/larastan/v/stable.svg" alt="Latest Version"></a>
  <a href="https://github.com/larastan/larastan/blob/master/LICENSE.md"><img src="https://poser.pugx.org/larastan/larastan/license.svg" alt="License"></a>
</p>

------

## ⚗️ About Laravel Eraser

 Laravel Eraser focuses on **Erasing Eloquent Model Relation**.
 It has to mode of operation.

- Manual mode:this mode require the developer to defien eraserRelationsToDelete and contain relations the eraser should delete
- Auto mode: this mode use auto-discove for children relations to be deleted

> While by definition, "static analysis" doesn't load any of your application's code. Larastan boots your application's container, so it can resolve types that are only possible to compute at runtime. That's why we use the term "code analysis" instead of "static analysis".

### Supported Laravel versions
| Laravel Version    | Erase Version    |
|--------------------|------------------|
| 12.x               | 1.0+             |


## ✨ Getting Started In 3 Steps

> **Requires:**
- **[PHP 8.2+](https://php.net/releases/)**
- **[Laravel 12.x+](https://github.com/laravel/laravel)**

**1**: First, you may use [Composer](https://getcomposer.org) to install laravel-eraser into your Laravel project:

```bash
composer require "hith/laravel-eraser:^1.0"
```

**2**: Then, publish config file:

```bash
	php artisan vendor:publish --tag=eraser-config
```

**3**: Finally, Use the package in two ways::
-By adding traits directly to your models.

-By using the Eraser class to delete/clean models.

#### The following section will discuss the usage of traits:
-there are two types of traits [manual , auto]:

### Manual Eraser Trait:
	usage of this trait in the model:

```php
use Hith\LaravelEraser\Traits\HasManualEraser;

class Post extends Model
{
    use HasManualEraser;

    public array $eraserRelationsToDelete = ['comments','tags'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): HasMany
    {
        return $this->blongsToMany(Comment::class);
    }
}

```

Then instead of:
```php
$post->comments()->delete();
$post->tags()->detach();
$post->delete();
```

Simply do:
```php
$post->delete();
```

Or, if you just want to clear relations without deleting the model:
```php
$post->clean();
```

### Auto Eraser Trait:
	usage of this trait in the model:

```php
use Hith\LaravelEraser\Traits\HasAutoEraser;

class Post extends Model
{
    use HasAutoEraser;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): HasMany
    {
        return $this->blongsToMany(Comment::class);
    }
}

```

as with the other one simply do this:
```php
$post->clean();
// or
$post->delete();
```

#### The following section will discuss the usage of Eraser Facade:

- Manual Mode (default):
```php
use Hith\LaravelEraser\Eraser;

$eraser = new Eraser();    // Manual is the default
$eraser->clean($model);    // cleans relations only
$eraser->delete($model);   // or cleans relations and deletes model

```

- Auto Mode
```php
use Hith\LaravelEraser\Eraser;

$eraser = new Eraser('auto');    // set eraser to auto
$eraser->clean($model);
$eraser->delete($model);

```

-[Note]: The user() relation will not be deleted in either mode, as it is considered a parent relation.

## Eraser Configration

You can alter the behavior of the eraser either globaly using the eraser config file
or by defingin some of this attributes on you model.

```php
/**
* Determines whether the "eraser" should automatically start deleting
* related models when the parent model is being deleted.
* Default: true
* */
public bool $eraser_onDeleteStart = true;

/**
 * Controls whether relations to be deleted should be automatically
 * discovered when using the Auto Eraser functionality.
 * Default: true
 * */
public bool $eraser_autoDiscover = true;

/**
 * Enables or disables global logging for eraser operations.
 * Default: true
 * */
public bool $eraser_logging = false;

/**
 * Set logger for model logs
 * */
public callable|LoggerInterface $logger;

```

## Best practices

Logging: Keep logging enabled in development/staging; set a dedicated eraser_log_channel for easier monitoring.

Testing: Validate deletion flows, including parent relations and many-to-many detaches, before production.

Deny list: Extend eraser_base_deny_list to match your naming patterns for utility methods.

Mode choice: Use manual mode for sensitive domains (users, orders); auto mode for convenience on simpler models.

Ownership safety: Parent relations (BelongsTo, MorphTo) are skipped to preserve ownership chains
and to prevent circular deletion loop.

Pivot hygiene: Many-to-many are detached; ensure pivot constraints and cascades are correct.

Model Relationships:

In order for Eraser (auto-discover) to recognize Model relationships the following is recomended:

- the return type must be defined
- the method must be `public`
- the mathod has no arguments

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}

public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}
```

## Log Outputs Example

Info
[info] Processing: Post[42]
[info] Processing relation 'comments'
[info] Relation 'comments' processed

Skipped
[info] Skipping parent relation 'author'
[info] Skipping already processed Post:42

Warning
[warning] Missing 'eraserRelationsToDelete' on Post
[warning] Method 'likes' did not return a Relation

Error
[error] Relation 'tags' not found on Post
[error] Error deleting relation 'images': Call to undefined method

## 👊🏻 Contributing

Thank you for considering contributing to Laravel Eeraser. All the contribution guidelines are mentioned [here](CONTRIBUTING.md).

## 📖 License

Laravel Eraser is an open-sourced software licensed under the [MIT license](LICENSE.md).
