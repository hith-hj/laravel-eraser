<?php

declare(strict_types=1);

use Eraser\Eraser;
use Eraser\Facades\Erase;
use Eraser\Tests\Fixtures\Manual\Comment;
use Eraser\Tests\Fixtures\Manual\Post;
use Eraser\Tests\Fixtures\Manual\User;

beforeEach(function () {
    $this->setDB();
    $user = User::create();
    $post = Post::create(['user_id' => $user->id]);
    Comment::create(['user_id' => $user->id, 'post_id' => $post->id]);
    $this->user = $user;
});

it('defaults to manual type', function () {
    $eraser = new Eraser;
    expect($eraser->type)->toBe('manual');
});

it('can change types', function () {
    $eraser = new Eraser('auto');
    expect($eraser->type)->toBe('auto');
    $eraser->type('manual');
    expect($eraser->type)->toBe('manual');
});

it('throws exception for invalid type', function () {
    expect(function () {
        $eraser = new Eraser;
        $eraser->type('invalid');
    })->toThrow(InvalidArgumentException::class, 'Invalid Eraser Type: invalid');
});

it('delete only relation specified in erasable', function () {
    $this->user->erasable = ['comments'];
    (new Eraser)->clean($this->user);
    expect($this->user->comments()->count())->toBe(0)
        ->and($this->user->posts()->count())->toBe(1)
        ->and($this->user->posts[0]->comments()->count())->toBe(0);
});

it('delete relation and model', function () {
    $this->user->erasable = ['posts'];
    (new Eraser)->delete($this->user);
    expect($this->user->comments()->count())->toBe(0)
        ->and($this->user->posts()->count())->toBe(0)
        ->and($this->user->fresh())->toBeNull();
});

it('cant delete relation not specified in erasable', function () {
    $this->user->erasable = [];
    (new Eraser)->clean($this->user);
    expect($this->user->comments()->count())->toBe(1)
        ->and($this->user->posts()->count())->toBe(1)
        ->and($this->user->posts[0]->comments()->count())->toBe(1);
});

it('log correctly if logger enabled', function () {

    $logger = $this->logger();
    $this->user->eraserLogger = $logger;
    $this->user->eraserLoggingEnabled = true;

    (new Eraser)->clean($this->user);
    $this->assertNotEmpty($logger->messages);
    $this->assertSame('info', $logger->messages[0][0]);
});

it('dont log if disabeled', function () {
    $logger = $this->logger();
    $this->user->eraserLogger = $logger;
    $this->user->eraserLogging = false;
    (new Eraser)->clean($this->user);

    $this->assertEmpty($logger->messages);
});

it('cascade delete on models', function () {
    $this->user->erasable = ['posts'];
    $post = $this->user->posts[0];
    (new Eraser)->clean($this->user);
    expect($this->user->posts()->count())->toBe(0)
        ->and($post->comments()->count())->toBe(0)
        ->and(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});

it('can use facade to clean relation', function () {
    $this->user->erasable = ['posts'];
    $post = $this->user->posts[0];
    Erase::clean($this->user);
    expect($this->user->posts()->count())->toBe(0)
        ->and($post->comments()->count())->toBe(0)
        ->and(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});

it('can use facade to delete relation', function () {
    $this->user->erasable = ['posts'];
    $post = $this->user->posts[0];
    Erase::delete($this->user);
    expect($this->user->posts()->count())->toBe(0)
        ->and($post->comments()->count())->toBe(0)
        ->and(Post::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});
