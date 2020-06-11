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
    public $trackingId = null;

    public $goalId = null;
}
