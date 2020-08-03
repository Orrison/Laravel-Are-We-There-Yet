<?php

namespace Orrison\AreWeThereYet\Tests\Feature;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Orrison\AreWeThereYet\Middleware\TrackedMiddleware;
use Orrison\AreWeThereYet\Models\AwtyTask;
use Orrison\AreWeThereYet\Tests\Data\TestCompletionJob;
use Orrison\AreWeThereYet\Tests\Data\TestJobOne;
use Orrison\AreWeThereYet\Tests\Data\TestJobThree;
use Orrison\AreWeThereYet\Tests\Data\TestJobTwo;
use Orrison\AreWeThereYet\Tests\TestCase;

class ParallelDispatchTest extends TestCase
{
    public function testThatParallelDispatchQueuesTheJobCluster()
    {
        Queue::fake();

        parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        Queue::assertPushed(TestJobOne::class);
        Queue::assertPushed(TestJobTwo::class);
        Queue::assertPushed(TestJobThree::class);
    }

    public function testThatParallelDispatchStoresAGoalInDatabase()
    {
        Queue::fake();

        $dispatchData = parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        $this->assertDatabaseHas('awty_goals', [
            'uniqueGoalKey' => $dispatchData['goalId'],
        ]);
    }

    public function testThatParallelDispatchStoresTheTasksInDatabase()
    {
        Queue::fake();

        $dispatchData = parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        foreach ($dispatchData['taskKeys'] as $taskId) {
            $this->assertDatabaseHas('awty_tasks', [
                'uniqueGoalKey' => $dispatchData['goalId'],
                'uniqueTaskKey' => $taskId,
            ]);
        }
    }

    public function testThatTheCompletionJobIsTriggeredIfAllJobsAreComplete()
    {
        // TODO: Somehow eventually test specifically if TestCompletionJob is dispatched. For now just test if it sets the cache value

        $dispatchData = parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        $this->assertEquals('asd', Cache::get('test'));
    }

    // TODO: Write tests for chained job dispatches
}