<?php

namespace Orrison\AreWeThereYet\Traits;

use Orrison\AreWeThereYet\Middleware\TaskedMiddleware;
use Orrison\AreWeThereYet\TaskedJob;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\MaxAttemptsExceededException;
use Orrison\AreWeThereYet\Goal;

trait Taskable
{
    /**
     * TaskedJob tied to this job.
     *
     * @var \Orrison\AreWeThereYet\TaskedJob
     */
    public $taskedJob = null;

    public $goalId = null;

    public function middleware()
    {
        return [new TaskedMiddleware()];
    }

    public static function dispatchAsTask($goalId, $args)
    {
        // TODO: Create a goal if it does not already exist
        Goal::firstOrCreate(
            ['goal_id' => $goalId],
            ['completed' => false],
        );

        $taskedJob = TaskedJob::create([
            'goal_id' => $goalId,
            'task_name' => class_basename(static::class),
        ]);

        $newClass = new static(...$args);
        $newClass->goalId = $goalId;
        $newClass->taskedJob = $taskedJob;

        return new PendingDispatch($newClass);
    }

    /**
     * Handle the job failing by marking the deployment as failed.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        if (is_null($this->taskedJob)) {
            return;
        }

        $message = $exception->getMessage();

        if ($exception instanceof MaxAttemptsExceededException) {
            $message = 'This operation took too long.';
        }

        $this->taskedJob->markAsFailed($message . PHP_EOL . $this->appendToFailureOutput());
    }
}
