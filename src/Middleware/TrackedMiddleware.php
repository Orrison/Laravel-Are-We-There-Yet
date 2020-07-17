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
     */
    public function handle($job, $next)
    {
        if (isset($job->trackingId) && isset($job->goalId)) {
            try {
                $response = $next($job);

                if ($this->wasSuccesful($job)) {
                    $goalObject = Cache::tags(['awty'])->get($job->goalId);

                    $pos = array_search($job->trackingId, $goalObject['tasks']);
                    if ($pos !== false) {
                        unset($goalObject['tasks'][$pos]);
                    } else {
                        Log::warning($job->trackingId . ' not found');
                    }

                    if (empty($goalObject['tasks'])) {
                        dispatch($goalObject['completionJob']);
                        Cache::tags(['awty'])->forget($job->goalId);
                    } else {
                        Cache::tags(['awty'])->put($job->goalId, $goalObject);
                    }
                }
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
    protected function wasSuccesful($job)
    {
        return !$job->job->hasFailed();
    }
}
