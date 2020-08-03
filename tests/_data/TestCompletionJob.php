<?php

namespace Orrison\AreWeThereYet\Tests\Data;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
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
        Cache::put('test','asd', 300);
    }
}