<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Resetter\AppStateResetterInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateResetListener extends AbstractTerminateListener
{
    /**
     * @var \EonX\EasySwoole\Common\Resetter\AppStateResetterInterface[]
     */
    private array $appStateResetters;

    /**
     * @param iterable<\EonX\EasySwoole\Common\Resetter\AppStateResetterInterface> $appStateResetters
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
