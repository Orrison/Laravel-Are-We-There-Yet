<?php

namespace Orrison\AreWeThereYet\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Orrison\AreWeThereYet\Models\AwtyGoal;
use Orrison\AreWeThereYet\Models\AwtyTask;
use Orrison\AreWeThereYet\Traits\Trackable;

class AreWeThereYetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/awty.php' => config_path('awty.php'),
        ], 'awty-config');

        $this->loadMigrationsFrom(__DIR__.'/../Migrations');

        Queue::after(function (JobProcessed $event) {
            if ($this->isUsingTrackableTrait($event)) {
                $jobObject = unserialize($event->job->payload()['data']['command']);
                if ($this->isTrackingSet($jobObject)) {
                    if ($this->wasSuccessful($event->job)) {
                        $goal = AwtyGoal::where(['uniqueGoalKey' => $jobObject->goalId])->firstOrFail();

                        $task = AwtyTask::where([
                            'uniqueGoalKey' => $jobObject->goalId,
                            'uniqueTaskKey' => $jobObject->trackingId
                        ])->firstOrFail();

                        $task->update([
                            'completed' => now(),
                        ]);

                        $remainingTasks = AwtyTask::where(['uniqueGoalKey' => $jobObject->goalId])->whereNull('completed')->get();

                        if ($remainingTasks->isEmpty()) {
                            dispatch($goal->completionJob);
                            $goal->update([
                                'completed' => now(),
                            ]);
                        }
                    }
                }
            }
        });
    }

    /**
     * Check if this job is using our Trackable Trait
     *
     * @param $event
     * @return bool
     */
    public function isUsingTrackableTrait($event)
    {
        return in_array(Trackable::class, class_uses(unserialize($event->job->payload()['data']['command'])));
    }

    /**
     * Check if Tracking ids are set
     *
     * @param $jobObject
     * @return bool
     */
    public function isTrackingSet($jobObject)
    {
        if (isset($jobObject->trackingId) && isset($jobObject->goalId)) {
            return true;
        }
        return false;
    }

    /**
     * @param $job
     * @return bool
     */
    public function wasSuccessful($job)
    {
        return !$job->hasFailed();
    }
}
