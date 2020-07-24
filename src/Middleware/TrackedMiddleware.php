<?php

namespace Orrison\AreWeThereYet\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrackedMiddleware
{
    /**
     * Wrap the Trackable job with a try/catch and logic to ensure it is tracked.
     *
     * @param mixed $job
     * @param callable $next
     * @return void
     * @throws \Throwable
     */
    public function handle($job, $next)
    {
        if (isset($job->trackingId) && isset($job->goalId)) {
            $response = $next($job);

            if ($this->wasSuccessful($job)) {
                $goalObject = Cache::get($job->goalId);

                $pos = array_search($job->trackingId, $goalObject['tasks']);
                if ($pos !== false) {
                    unset($goalObject['tasks'][$pos]);
                } else {
                    Log::warning($job->trackingId . ' not found');
                }

                if (empty($goalObject['tasks'])) {
                    dispatch($goalObject['completionJob']);
                    Cache::forget($job->goalId);
                } else {
                    Cache::put($job->goalId, $goalObject, config('awty.expire', 2592000));
                }
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
