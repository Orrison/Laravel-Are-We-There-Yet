<?php

namespace Orrison\AreWeThereYet\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;
use Orrison\AreWeThereYet\TaskedJob;

class TaskedJobFinished
{
    use SerializesModels;

    public $taskedJob;

    /**
     * Create a new event instance.
     *
     * @param  \App\Order  $taskedJob
     * @return void
     */
    public function __construct(TaskedJob $taskedJob)
    {
        $this->taskedJob = $taskedJob;
    }
}
