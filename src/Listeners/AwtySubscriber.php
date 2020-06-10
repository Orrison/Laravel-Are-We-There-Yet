<?php

namespace Orrison\AreWeThereYet\Listeners;

class AwtySubscriber
{
    public function taskCompleted($event) {
        if ($event->taskedJob->goal_id) {

        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Orrison\AreWeThereYet\Events\TaskedJobFinished',
            'Orrison\AreWeThereYet\AwtySubscriber@taskCompleted'
        );
    }
}
