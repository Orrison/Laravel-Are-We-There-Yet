<?php

namespace Orrison\AreWeThereYet\Tests\Feature;

use Illuminate\Support\Facades\Queue;
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

        $goalId = parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        $this->assertDatabaseHas('awty_goals', [
            'uniqueGoalKey' => $goalId['goalId'],
        ]);
    }

    public function testThatParallelDispatchStoresTheTasksInDatabase()
    {
        Queue::fake();

        $goalId = parallelDispatch([
            new TestJobOne(),
            new TestJobTwo(),
            new TestJobThree(),
        ], new TestCompletionJob());

        foreach ($goalId['taskKeys'] as $taskId) {
            $this->assertDatabaseHas('awty_tasks', [
                'uniqueGoalKey' => $goalId['goalId'],
                'uniqueTaskKey' => $taskId,
            ]);
        }
    }
}