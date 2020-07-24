<?php

namespace Orrison\AreWeThereYet\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Orrison\AreWeThereYet\Models\AwtyGoal;
use Orrison\AreWeThereYet\Models\AwtyTask;

class TrackedMiddleware
{
    /**
     * Wrap the Trackable job with a try/catch and logic to ensure it is tracked.
     *
     * @param mixed $job
     * @param callable $next
     * @return void
     */
    public function handle($job, $next)
    {
        if (isset($job->trackingId) && isset($job->goalId)) {
            try {
                $response = $next($job);

                if ($this->wasSuccessful($job)) {
                    $goal = AwtyGoal::where(['uniqueGoalKey' => $job->goalId])->firstOrFail();

                    $task = AwtyTask::where([
                        'uniqueGoalKey' => $job->goalId,
                        'uniqueTaskKey' => $job->trackingId
                    ])->firstOrFail();

                    $task->update([
                        'completed' => now(),
                    ]);

                    $remainingTasks = AwtyTask::where(['uniqueGoalKey' => $job->goalId])->whereNull('completed')->get();

                    if ($remainingTasks->isEmpty()) {
                        dispatch($goal->completionJob);
                        $goal->update([
                           'completed' => now(),
                        ]);
                    }
                }
                return $response;
            } catch (\Throwable $e) {
                $job->fail($e);
            }
        } else {
            $next($job);
        }
    }

    /**
     * @param $job
     * @return bool
     */
    protected function wasSuccessful($job)
    {
        return !$job->job->hasFailed();
    }
}
