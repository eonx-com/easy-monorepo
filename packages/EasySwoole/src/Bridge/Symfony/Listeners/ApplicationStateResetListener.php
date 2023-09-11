<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Interfaces\AppStateResetterInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateResetListener extends AbstractTerminateEventListener
{
    /**
     * @var \EonX\EasySwoole\Interfaces\AppStateResetterInterface[]
     */
    private array $appStateResetters;

    /**
     * @param iterable<\EonX\EasySwoole\Interfaces\AppStateResetterInterface> $appStateResetters
     */
    public function __construct(iterable $appStateResetters)
    {
        $this->appStateResetters = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateResetters, AppStateResetterInterface::class)
        );
    }

    protected function doInvoke(TerminateEvent $event): void
    {
        foreach ($this->appStateResetters as $appStateResetter) {
            $appStateResetter->resetState();
        }
    }
}
