<?php

namespace Orrison\AreWeThereYet\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Orrison\AreWeThereYet\Events\TaskedJobFinished;

class TaskedMiddleware
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
        Log::info('In Middleware');
        if (isset($job->trackingId) && !empty($job->trackingId) && isset($job->goalId) && !empty($job->goalId)) {
            try {
                $response = $next($job);

                // If the response is truthy, then we can assume
                // the taskedJob has been completed.
                if ($response) {
                    $goalObject = Cache::tags(['awty'])->get($job->goalId);

                    $pos = array_search($job->trackingId, $goalObject['tasks']);
                    if ($pos) {
                        unset($goalObject['tasks'][$pos]);
                    } else {
                        // Probably log this
                    }

                    if (empty($goalObject['tasks'])) {
                        Log::info('No more tasks');
                        $goalObject['completionJob']::dispatch(...$goalObject['completionJobArgs']);
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
}
