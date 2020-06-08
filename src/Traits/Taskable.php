<?php

namespace Orrison\AreWeThereYet\Traits;

use Orrison\AreWeThereYet\Middleware\TaskedMiddleware;
use Orrison\AreWeThereYet\TaskedJob;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\MaxAttemptsExceededException;

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
        return [new Tasked()];
    }

    public static function dispatchAsTask($goalId, $args)
    {
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
