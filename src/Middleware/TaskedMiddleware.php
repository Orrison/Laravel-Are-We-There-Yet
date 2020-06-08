<?php

namespace Orrison\AreWeThereYet\Middleware;

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
        if (isset($job->taskedJob) && !empty($job->taskedJob)) {
            $job->taskedJob->markAsStarted();

            try {
                $response = $next($job);

                // If the response is truthy, then we can assume
                // the taskedJob has been completed.
                if ($response) {
                    $job->taskedJob->markAsFinished($response);
                }
            } catch (\Throwable $e) {
                $job->fail($e);
            }
        } else {
            $next($job);
        }
    }
}
