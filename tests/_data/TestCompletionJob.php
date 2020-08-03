<?php

namespace Orrison\AreWeThereYet\Tests\Data;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Orrison\AreWeThereYet\Traits\Trackable;

class TestCompletionJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use Dispatchable;
    use Trackable;

    public function __construct()
    {
    }

    public function handle()
    {
    }
}