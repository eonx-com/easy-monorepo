<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Interfaces;

interface EventListenerInterface
{
    /**
     * @return \EonX\EasyDoctrine\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface[]
     */
    public function registerEvents(): array;
}
