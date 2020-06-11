# Are We There Yet?

Easy to use helper method to dispatch a list of jobs and job chains that, upon completion of all of them, trigger one last final job. Often we dispatch jobs that can run in parallel but that once all complete require something else to be done. Now you can!

With the `parallelDispatch` helper method you can dispatch a list of jobs AND chained jobs that will fire your defined "completion job" once they have all been completed successfully.

```php
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
    function parallelDispatch($jobList, $uniqueGoalKey, $completionJob, $completionJobArgs)
```

## Examples:

### Running a list of jobs in parallel
`SomeJobToBeRunAfter` will be run once they are all completed with the arguments `$completionjobArg` and `$completionjobArg` being passed into `SomeJobToBeRunAfter`.
```php
    $uniqueGoalId = Str::random(15);

    parallelDispatch(
        [
            new JobOne(),
            new JobTwo($arg1, $arg2),
        ],
        $uniqueGoalId,
        'App\Jobs\SomeJobToBeRunAfter',
        [$completionjobArg, $completionjobArg]
    );
```

### Running a list of jobs including a job chain
Jobs in the chain can also be dispatch by including a sub array of job objects in the main `$jobList`. The will be chained in the order they are listed.
```php
    $uniqueGoalId = Str::random(15);

    parallelDispatch(
        [
            new JobOne(),
            [
                new chainedJobOne($arg1),
                new chainedJobTwo(),
            ],
            new JobTwo($arg1, $arg2),
        ],
        $uniqueGoalId,
        'App\Jobs\SomeJobToBeRunAfter',
        [$completionjobArg, $completionjobArg]
    );
```
In the above example `JobOne`, `JobTwo`, and `chainedJobOne` will be dispatched immediatly. But `chainedJobTwo` and any others in that array will be chained to `chainedJobOne` and will only complete in the sequential order they are listed.