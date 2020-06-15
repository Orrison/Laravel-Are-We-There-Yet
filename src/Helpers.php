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
     * @param  string $uniqueGoalKey A unique key to track a particular goal instance
     * @param  string $completionJob A namespaced path to the job you would like run once all tracked jobs complete
     * @param  array $completionJobArgs The arguments in order to be passed to the $completionJob
     * @return void
     */
    function parallelDispatch($jobList, $uniqueGoalKey, $completionJob)
    {
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

        Cache::tags(['awty'])->put($uniqueGoalKey, [
            'completionJob' => $completionJob,
            'tasks' => $tasks,
        ]);

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
