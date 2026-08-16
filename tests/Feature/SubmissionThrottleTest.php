<?php

use App\Support\SubmissionThrottle;
use Illuminate\Support\Facades\RateLimiter;

it('allows submissions up to the cap and consumes one attempt each time', function () {
    RateLimiter::clear('test:throttle');

    expect(SubmissionThrottle::attempt('test:throttle'))->toBeNull()
        ->and(SubmissionThrottle::attempt('test:throttle'))->toBeNull()
        ->and(SubmissionThrottle::attempt('test:throttle'))->toBeNull();
});

it('returns the wait time once the cap is reached, without consuming further attempts', function () {
    RateLimiter::clear('test:throttle-full');

    foreach (range(1, 3) as $attempt) {
        SubmissionThrottle::attempt('test:throttle-full');
    }

    $first = SubmissionThrottle::attempt('test:throttle-full');
    $second = SubmissionThrottle::attempt('test:throttle-full');

    expect($first)->toBeInt()->toBeGreaterThan(0)
        ->and($second)->toBeInt()->toBeGreaterThan(0)
        ->and(RateLimiter::attempts('test:throttle-full'))->toBe(3);
});

it('keeps the throttle per key', function () {
    RateLimiter::clear('test:key-a');
    RateLimiter::clear('test:key-b');

    foreach (range(1, 3) as $attempt) {
        SubmissionThrottle::attempt('test:key-a');
    }

    expect(SubmissionThrottle::attempt('test:key-a'))->not->toBeNull()
        ->and(SubmissionThrottle::attempt('test:key-b'))->toBeNull();
});
