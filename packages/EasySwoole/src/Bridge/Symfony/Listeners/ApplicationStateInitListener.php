<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Interfaces\AppStateInitializerInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ApplicationStateInitListener extends AbstractRequestEventListener
{
    /**
     * @var \EonX\EasySwoole\Interfaces\AppStateInitializerInterface[]
     */
    private array $appStateInitializers;

    /**
     * @param iterable<\EonX\EasySwoole\Interfaces\AppStateInitializerInterface> $appStateInitializers
     */
    public function __construct(iterable $appStateInitializers)
    {
        $this->appStateInitializers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateInitializers, AppStateInitializerInterface::class)
        );
    }

    protected function doInvoke(RequestEvent $event): void
    {
        foreach ($this->appStateInitializers as $appStateInitializer) {
            $appStateInitializer->initState();
        }
    }
}
