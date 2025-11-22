<?php

use Hith\LaravelEraser\Tests\Fixtures\Auto\Comment;
use Hith\LaravelEraser\Tests\Fixtures\Auto\Post;
use Hith\LaravelEraser\Tests\Fixtures\Auto\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->setDB();
});

it('delete related relation when model is deleted', function () {
    $user = User::create();
    $post = $user->posts()->create();
    $post->comments()->create(['user_id' => $user->id]);

    $user->delete();

    expect(User::count())->toBe(0)
        ->and(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});

it('delete related relation when model is cleaned', function () {
    $user = User::create();
    $post = $user->posts()->create();
    $post->comments()->create(['user_id' => $user->id]);

    $user->clean();

    expect(User::count())->toBe(1)
        ->and(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});

it('cant delete relation when auto-discover is disabled', function () {
    $user = User::create();
    $post = $user->posts()->create();
    $post->comments()->create(['user_id' => $user->id]);
    $user->eraserAutoDiscover = false;
    $user->clean();

    expect(User::count())->toBe(1)
        ->and(Post::count())->toBe(1)
        ->and(Comment::count())->toBe(1);
});

it('cant cascade relation delete when disabled on model', function () {
    $user = User::create();
    $post = $user->posts()->create();
    $post->comments()->create(['user_id' => $user->id]);
    $post->eraserOnDeleteStart = false;
    $post->delete();

    expect(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(1);
});
