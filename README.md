# Are We There Yet?

Easy to use helper method to dispatch a list of jobs and job chains that, upon completion of all of them, trigger one last final job. Often we dispatch jobs that can run in parallel but that once all complete require something else to be done.

With the `parallelDispatch` helper method you can dispatch a list of jobs AND chained jobs that will fire your defined "completion job" once they have all been completed successfully.

```php
    /**
     * parallelDispatch
     *
     * @param mixed $jobList An array of job objects you would like dispatched and tracked.
     * Adding a multidimensional array will dispatch the sub-array in a job chain in the order they are listed
     * @param object $completionJob A fully instantiated class for the job to be run once all other jobs in the job list have completed.
     * @return void
     */
    function parallelDispatch($jobList, $completionJob)
```

## Installation & Setup:

Install via composer using the following command:

```shell script
composer require orrison/laravel-are-we-there-yet
```

Two tables are required for the tracking `awty_goals` and `awty_tasks`. They can be added by running a migration after installation.

```shell script
php artisan migrate
```

After the package and tables are setup you may now dispatch a cluster of jobs via `parallelDispatch` that once all completed will trigger the "completion job".
All jobs included in the cluster MUST have the `Trackable` trait. The completion job does NOT require the trait.

## Examples:

### Running a list of jobs in parallel
`SomeJobToBeRunAfter` will be run once they are all completed.
```php
    parallelDispatch(
        [
            new JobOne(),
            new JobTwo($arg1, $arg2),
        ],
        new SomeJobToBeRunAfter($someJobArg)
    );
```

### Running a list of jobs including a job chain
Jobs in the chain can also be dispatch by including a sub array of job objects in the main `$jobList`. The will be chained in the order they are listed.
```php
    parallelDispatch(
        [
            new JobOne(),
            [
                new chainedJobOne($arg1),
                new chainedJobTwo(),
            ],
            new JobTwo($arg1, $arg2),
        ],
        new SomeJobToBeRunAfter($someJobArg)
    );
```
In the above example `JobOne`, `JobTwo`, and `chainedJobOne` will be dispatched immediately. But `chainedJobTwo` and any others in that array will be chained to `chainedJobOne` and will only complete in the sequential order they are listed.

Based on Job Tracking from: https://github.com/rafter-platform/rafter

### Testing

``` bash
composer test
```