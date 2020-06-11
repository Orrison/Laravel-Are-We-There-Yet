<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orrison\AreWeThereYet\Middleware\TaskedMiddleware;

if (! function_exists('parallelDispatch')) {
    function parallelDispatch($jobList, $uniqueGoalKey, $completionJob)
    {
        $tasks = [];
        foreach ($jobList as $rootKey => $possibleJob) {
            if (is_array($possibleJob)) {
                foreach ($possibleJob as $subKey => $job) {
                    $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);
                    array_push($tasks, $uniqueTaskKey);
                    $jobList[$rootKey][$subKey]->trackingId = $uniqueTaskKey;
                    $jobList[$rootKey][$subKey]->goalId = $uniqueGoalKey;
                    
                    if (is_array($jobList[$rootKey][$subKey]->middleware)) {
                        array_push($jobList[$rootKey][$subKey]->middleware, new TaskedMiddleware());
                    } else {
                        $jobList[$rootKey][$subKey]->middleware = [new TaskedMiddleware()];
                    }
                }
            } else {
                $uniqueTaskKey = $uniqueGoalKey . '-' . Str::random(10);
                array_push($tasks, $uniqueTaskKey);
                $jobList[$rootKey]->trackingId = $uniqueTaskKey;
                $jobList[$rootKey]->goalId = $uniqueGoalKey;

                if (is_array($jobList[$rootKey]->middleware)) {
                    array_push($jobList[$rootKey]->middleware, new TaskedMiddleware());
                } else {
                    $jobList[$rootKey]->middleware = [new TaskedMiddleware()];
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
