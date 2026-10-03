<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;

function scheduledEventFor(string $commandName): ?Event
{
    app(Kernel::class)->bootstrap();

    return collect(app(Schedule::class)->events())
        ->first(fn (Event $event) => str_contains((string) $event->command, $commandName));
}

test('seo meta generation is scheduled daily at 3am Eastern', function () {
    $event = scheduledEventFor('app:process-threads-for-seo');

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 3 * * *');
    expect($event->timezone)->toBe('America/New_York');
});

test('seo meta generation does not run when the feature flag is off', function () {
    config(['services.openai.seo_meta_generation_enabled' => false]);

    $event = scheduledEventFor('app:process-threads-for-seo');

    expect($event)->not->toBeNull();
    expect($event->filtersPass($this->app))->toBeFalse();
});

test('seo meta generation runs when the feature flag is on', function () {
    config(['services.openai.seo_meta_generation_enabled' => true]);

    $event = scheduledEventFor('app:process-threads-for-seo');

    expect($event)->not->toBeNull();
    expect($event->filtersPass($this->app))->toBeTrue();
});

test('seo meta generation flag is off by default', function () {
    expect(config('services.openai.seo_meta_generation_enabled'))->toBeFalse();
});

test('daily wordle puzzle is still scheduled at midnight', function () {
    $event = scheduledEventFor('wordle:create-daily-puzzle');

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 0 * * *');
});
