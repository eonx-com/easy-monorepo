<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel\Listeners;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyCore\Bridge\Laravel\Listeners\DoctrineRestartQueueOnEmCloseListener;
use EonX\EasyCore\Tests\AbstractTestCase;
use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Laravel\Listeners\DoctrineRestartQueueOnEmCloseListener
 *
 * @internal
 */
final class DoctrineRestartQueueOnEmCloseListenerTest extends AbstractTestCase
{
    /**
     * Test `handle` succeeds.
     */
    public function testHandleSucceeds(): void
    {
        $jobProphecy = $this->prophesize(Job::class);
        $jobProphecy->getJobId()->willReturn('job_id');
        $jobProphecy->getName()->willReturn('job_name');
        /** @var \Illuminate\Contracts\Queue\Job $job */
        $job = $jobProphecy->reveal();
        $event = new JobExceptionOccurred('connectionName', $job, new Exception('some-message'));
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->isOpen()->willReturn(false);
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $emProphecy->reveal();
        Carbon::setTestNow(Carbon::create(2020, 5, 3));
        $cacheProphecy = $this->prophesize(Repository::class);
        $cacheProphecy->forever('illuminate:queue:restart', Carbon::now()->getTimestamp())->willReturn(true);
        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $cacheProphecy->reveal();
        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $logData = [
            'connection' => $event->connectionName,
            'job_id' => 'job_id',
            'job_name' => 'job_name',
        ];
        $logMessage = 'Restarting queue because em is closed.';
        $loggerProphecy->info($logMessage, $logData);
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $loggerProphecy->reveal();
        $listener = new DoctrineRestartQueueOnEmCloseListener($entityManager, $cache, $logger);

        $listener->handle($event);

        $emProphecy->isOpen()->shouldHaveBeenCalledOnce();
        $cacheProphecy->forever('illuminate:queue:restart', Carbon::now()->getTimestamp())->shouldHaveBeenCalledOnce();
        $loggerProphecy->info($logMessage, $logData)->shouldHaveBeenCalledOnce();
    }

    /**
     * Test `handle` with open EntityManager succeeds.
     */
    public function testHandleWithOpenEmSucceeds(): void
    {
        /** @var \Illuminate\Contracts\Queue\Job $job */
        $job = $this->prophesize(Job::class)->reveal();
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->isOpen()->willReturn(true);
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $emProphecy->reveal();
        $cacheProphecy = $this->prophesize(Repository::class);
        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->prophesize(Repository::class)->reveal();
        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $loggerProphecy->reveal();
        $listener = new DoctrineRestartQueueOnEmCloseListener($entityManager, $cache, $logger);

        $listener->handle(new JobExceptionOccurred('connectionName', $job, new Exception('some-message')));

        $emProphecy->isOpen()->shouldHaveBeenCalledOnce();
        $cacheProphecy->forever(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
        /** @var mixed[] $infoData */
        $infoData = Argument::any();
        $loggerProphecy->info(Argument::any(), $infoData)->shouldNotHaveBeenCalled();
    }
}
