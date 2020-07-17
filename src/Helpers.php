<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orrison\AreWeThereYet\Middleware\TrackedMiddleware;

if (! function_exists('parallelDispatch')) {
    /**
     * parallelDispatch
     *
     * @param  mixed $jobList An array of job objects you would like dispatched and tracked.
     * Adding a multidimensional array will dispatch the sub-array in a job chain in the order they are listed
     * @param object $completionJob A fully instantiated class for the job to be run once all other jobs in the job list have completed.
     * @return void
     */
    function parallelDispatch($jobList, $completionJob)
    {
        // Create a unique key to track this specific Goal Chain
        $uniqueGoalKey = Str::random(20);

        $tasks = [];
        foreach ($jobList as $rootKey => $possibleJob) {
            // If the value is an array then it is a chained job. Set it up for that
            if (is_array($possibleJob)) {
                foreach ($possibleJob as $subKey => $job) {
                    $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);
                    array_push($tasks, $uniqueTaskKey);
                    $jobList[$rootKey][$subKey]->trackingId = $uniqueTaskKey;
                    $jobList[$rootKey][$subKey]->goalId = $uniqueGoalKey;
                    
                    if (is_array($jobList[$rootKey][$subKey]->middleware)) {
                        array_push($jobList[$rootKey][$subKey]->middleware, new TrackedMiddleware());
                    } else {
                        $jobList[$rootKey][$subKey]->middleware = [new TrackedMiddleware()];
                    }
                }
            } else {
                $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);
                array_push($tasks, $uniqueTaskKey);
                $jobList[$rootKey]->trackingId = $uniqueTaskKey;
                $jobList[$rootKey]->goalId = $uniqueGoalKey;

                if (is_array($jobList[$rootKey]->middleware)) {
                    array_push($jobList[$rootKey]->middleware, new TrackedMiddleware());
                } else {
                    $jobList[$rootKey]->middleware = [new TrackedMiddleware()];
                }
            }
        }

        Cache::put($uniqueGoalKey, [
            'completionJob' => $completionJob,
            'tasks' => $tasks,
        ], config('awty.expire', 2592000));

        foreach ($jobList as $rootKey => $possibleJob) {
            if (is_array($possibleJob)) {
                $firstJob = $possibleJob[0];
                array_shift($possibleJob);
                $firstJob->chain($possibleJob);
                dispatch($firstJob);
            } else {
                dispatch($possibleJob);
            }
        }
    }
}
