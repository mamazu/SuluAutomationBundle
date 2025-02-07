<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\AutomationBundle\Tests\Unit\PageTree;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\AutomationBundle\PageTree\AutomationPageTreeUpdater;
use Sulu\Bundle\AutomationBundle\PageTree\PageTreeRouteUpdateHandler;
use Sulu\Bundle\AutomationBundle\SuluAutomationBundle;
use Sulu\Bundle\AutomationBundle\Tasks\Manager\TaskManagerInterface;
use Sulu\Bundle\AutomationBundle\Tasks\Model\TaskInterface;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Bundle\RouteBundle\PageTree\PageTreeUpdaterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AutomationPageTreeUpdaterTest extends TestCase
{
    /**
     * @var TaskManagerInterface
     */
    private \Prophecy\Prophecy\ObjectProphecy $taskManager;

    /**
     * @var EntityManagerInterface
     */
    private \Prophecy\Prophecy\ObjectProphecy $entityManager;

    /**
     * @var RequestStack
     */
    private \Prophecy\Prophecy\ObjectProphecy $requestStack;

    /**
     * @var PageTreeUpdaterInterface
     */
    private \Sulu\Bundle\AutomationBundle\PageTree\AutomationPageTreeUpdater $updater;

    /**
     * @var Request
     */
    private \Prophecy\Prophecy\ObjectProphecy $request;

    public function setUp(): void
    {
        if (!\class_exists(SuluAutomationBundle::class)) {
            $this->markTestSkipped('SuluAutomationBundle is needed for this tests');
        }

        $this->taskManager = $this->prophesize(TaskManagerInterface::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->requestStack = $this->prophesize(RequestStack::class);

        $this->updater = new AutomationPageTreeUpdater($this->taskManager->reveal(), $this->entityManager->reveal(), $this->requestStack->reveal());

        $this->request = $this->prophesize(Request::class);
        $this->request->getScheme()->willReturn('http');
        $this->request->getHost()->willReturn('sulu.io');

        $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());
    }

    public function testUpdate(): void
    {
        $document = $this->prophesize(BasePageDocument::class);
        $document->getUuid()->willReturn('123-123-123');
        $document->getLocale()->willReturn('de');

        $task = $this->prophesize(TaskInterface::class);
        $this->taskManager->create(
            Argument::that(
                fn(TaskInterface $task): bool => BasePageDocument::class === $task->getEntityClass()
                    && '123-123-123' === $task->getEntityId()
                    && 'de' === $task->getLocale()
                    && PageTreeRouteUpdateHandler::class === $task->getHandlerClass()
                    && $task->getSchedule() <= new \DateTime()
                    && 'sulu.io' === $task->getHost()
                    && 'http' === $task->getScheme()
            )
        )->shouldBeCalled()->willReturn($task->reveal());

        $this->entityManager->flush()->shouldBeCalled();

        $this->updater->update($document->reveal());
    }
}
