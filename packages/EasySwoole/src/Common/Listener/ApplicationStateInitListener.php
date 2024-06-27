<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Initializer\AppStateInitializerInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ApplicationStateInitListener extends AbstractRequestListener
{
    /**
     * @var \EonX\EasySwoole\Common\Initializer\AppStateInitializerInterface[]
     */
    private array $appStateInitializers;

    /**
     * @param iterable<\EonX\EasySwoole\Common\Initializer\AppStateInitializerInterface> $appStateInitializers
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
