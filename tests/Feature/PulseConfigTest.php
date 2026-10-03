<?php

use Laravel\Pulse\Recorders\CacheInteractions;
use Laravel\Pulse\Recorders\Exceptions;
use Laravel\Pulse\Recorders\SlowQueries;
use Laravel\Pulse\Recorders\SlowRequests;

test('pulse does not record cache interactions', function () {
    expect(config('pulse.recorders.'.CacheInteractions::class.'.enabled'))->toBeFalse();
});

test('pulse keeps recording slow queries, slow requests, and exceptions', function (string $recorder) {
    expect(config('pulse.recorders.'.$recorder.'.enabled'))->toBeTruthy();
})->with([
    'slow queries' => SlowQueries::class,
    'slow requests' => SlowRequests::class,
    'exceptions' => Exceptions::class,
]);
