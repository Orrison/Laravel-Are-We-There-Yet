<?php

namespace Orrison\AreWeThereYet\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;

class TaskedJobFinished
{
    use SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}