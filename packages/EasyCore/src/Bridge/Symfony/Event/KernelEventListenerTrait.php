<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Event;

use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Event\KernelEventTag;

trait KernelEventListenerTrait
{
    /**
     * @return \EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface[]
     */
    public function registerEvents(): array
    {
        return [new KernelEventTag()];
    }
}
