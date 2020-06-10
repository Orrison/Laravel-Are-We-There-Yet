<?php

namespace Orrison\AreWeThereYet\Providers;

use Illuminate\Support\ServiceProvider;
use Orrison\AreWeThereYet\Events\TaskedJobFinished;

class EventsServiceProvider extends ServiceProvider
{
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\AwtySubscriber',
    ];
}
