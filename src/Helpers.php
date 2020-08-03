<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orrison\AreWeThereYet\Middleware\TrackedMiddleware;
use Orrison\AreWeThereYet\Models\AwtyGoal;
use Orrison\AreWeThereYet\Models\AwtyTask;

if (! function_exists('parallelDispatch')) {
    /**
     * parallelDispatch
     *
     * @param  mixed $jobList An array of job objects you would like dispatched and tracked.
     * Adding a multidimensional array will dispatch the sub-array in a job chain in the order they are listed
     * @param object $completionJob A fully instantiated class for the job to be run once all other jobs in the job list have completed.
     * @return array
     */
    function parallelDispatch($jobList, $completionJob)
    {
        // Create a unique key to track this specific Goal Chain
        $uniqueGoalKey = Str::random(20);
        $taskKeys = [];

        AwtyGoal::create([
            'uniqueGoalKey' => $uniqueGoalKey,
            'completionJob' => $completionJob,
        ]);

        $tasks = [];
        foreach ($jobList as $rootKey => $possibleJob) {
            // If the value is an array then it is a chained job. Set it up for that
            if (is_array($possibleJob)) {
                foreach ($possibleJob as $subKey => $job) {
                    $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);

                    $jobList[$rootKey][$subKey]->trackingId = $uniqueTaskKey;
                    $jobList[$rootKey][$subKey]->goalId = $uniqueGoalKey;
                    
//                    if (is_array($jobList[$rootKey][$subKey]->middleware)) {
//                        array_push($jobList[$rootKey][$subKey]->middleware, new TrackedMiddleware());
//                    } else {
//                        $jobList[$rootKey][$subKey]->middleware = [new TrackedMiddleware()];
//                    }

                    $taskKeys[] = $uniqueTaskKey;

                    AwtyTask::create([
                        'uniqueGoalKey' => $uniqueGoalKey,
                        'uniqueTaskKey' => $uniqueTaskKey,
                        'job' => $jobList[$rootKey][$subKey],
                    ]);
                }
            } else {
                $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);

                $jobList[$rootKey]->trackingId = $uniqueTaskKey;
                $jobList[$rootKey]->goalId = $uniqueGoalKey;

//                if (is_array($jobList[$rootKey]->middleware)) {
//                    array_push($jobList[$rootKey]->middleware, new TrackedMiddleware());
//                } else {
//                    $jobList[$rootKey]->middleware = [new TrackedMiddleware()];
//                }

                $taskKeys[] = $uniqueTaskKey;

                AwtyTask::create([
                    'uniqueGoalKey' => $uniqueGoalKey,
                    'uniqueTaskKey' => $uniqueTaskKey,
                    'job' => $jobList[$rootKey],
                ]);
            }
        }

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

        return [
            'goalId' => $uniqueGoalKey,
            'taskKeys' => $taskKeys,
        ];
    }
}
