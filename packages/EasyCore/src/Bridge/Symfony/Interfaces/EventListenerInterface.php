<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Interfaces;

interface EventListenerInterface
{
    /**
     * @return \EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface[]
     */
    public function registerEvents(): array;
}
