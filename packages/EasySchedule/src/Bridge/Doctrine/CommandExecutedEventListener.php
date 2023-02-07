<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasySchedule\Event\CommandExecutedEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

final class CommandExecutedEventListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(CommandExecutedEvent $event): void
    {
        $this->entityManager->clear();
    }
}
