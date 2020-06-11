<?php

namespace Orrison\AreWeThereYet\Traits;

trait Trackable
{
    /**
     * The unique trackingId for this job
     *
     */
    public $trackingId = null;

    /**
     * The unique goal for related to this job
     *
     */
    public $goalId = null;
}
