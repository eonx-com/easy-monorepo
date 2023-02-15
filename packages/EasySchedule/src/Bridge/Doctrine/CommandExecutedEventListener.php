<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasySchedule\Events\CommandExecutedEvent;

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
