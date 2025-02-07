<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\AutomationBundle\Tests\Unit\Tasks\Manager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\AutomationBundle\Events\Events;
use Sulu\Bundle\AutomationBundle\Events\TaskEvent;
use Sulu\Bundle\AutomationBundle\Tasks\Manager\TaskManager;
use Sulu\Bundle\AutomationBundle\Tasks\Manager\TaskManagerInterface;
use Sulu\Bundle\AutomationBundle\Tasks\Model\TaskInterface;
use Sulu\Bundle\AutomationBundle\Tasks\Model\TaskRepositoryInterface;
use Sulu\Bundle\AutomationBundle\Tasks\Scheduler\TaskSchedulerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests for task-manager.
 */
class TaskManagerTest extends TestCase
{
    /**
     * @var TaskRepositoryInterface
     */
    private \Prophecy\Prophecy\ObjectProphecy $taskRepository;

    /**
     * @var EventDispatcherInterface
     */
    private \Prophecy\Prophecy\ObjectProphecy $eventDispatcher;

    /**
     * @var TaskSchedulerInterface
     */
    private \Prophecy\Prophecy\ObjectProphecy $taskScheduler;

    /**
     * @var TaskManagerInterface
     */
    private \Sulu\Bundle\AutomationBundle\Tasks\Manager\TaskManager $taskManager;

    protected function setUp(): void
    {
        $this->taskRepository = $this->prophesize(TaskRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->taskScheduler = $this->prophesize(TaskSchedulerInterface::class);

        $this->taskManager = new TaskManager(
            $this->taskRepository->reveal(),
            $this->taskScheduler->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    public function testCreate(): void
    {
        $task = $this->prophesize(TaskInterface::class);
        $task->setId(Argument::type('string'))->shouldBeCalled();
        $this->taskScheduler->schedule($task->reveal())->shouldBeCalled();

        $this->assertEventDispatched(Events::TASK_CREATE_EVENT, $task->reveal());
        $this->taskRepository->save($task->reveal())->shouldBeCalled()->willReturnArgument(0);

        $this->taskManager->create($task->reveal());
    }

    public function testUpdate(): void
    {
        $task = $this->prophesize(TaskInterface::class);
        $this->taskScheduler->reschedule($task->reveal())->shouldBeCalled();

        $this->assertEventDispatched(Events::TASK_UPDATE_EVENT, $task->reveal());

        $this->taskManager->update($task->reveal());
    }

    public function testRemove(): void
    {
        $id = 1;
        $task = $this->prophesize(TaskInterface::class);
        $this->taskRepository->findById($id)->shouldBeCalled()->willReturn($task->reveal());
        $this->taskScheduler->remove($task->reveal())->shouldBeCalled();

        $this->assertEventDispatched(Events::TASK_REMOVE_EVENT, $task->reveal());
        $this->taskRepository->remove($task->reveal())->shouldBeCalled()->willReturnArgument(0);

        $this->taskManager->remove($id);
    }

    public function testFindById(): void
    {
        $id = 1;
        $task = $this->prophesize(TaskInterface::class);
        $this->taskRepository->findById($id)->shouldBeCalled()->willReturn($task->reveal());

        $this->assertEquals($task->reveal(), $this->taskManager->findById($id));
    }

    private function assertEventDispatched(string $eventName, $task): void
    {
        $this->eventDispatcher->dispatch(
            Argument::that(
                fn(TaskEvent $event): bool => $task == $event->getTask()
            ),
            $eventName
        )->willReturnArgument(0);
    }
}
