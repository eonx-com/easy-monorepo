<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Helpers\EasySwooleEnabledHelper;
use EonX\EasySwoole\Interfaces\ApplicationStateResetterInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateResetListener
{
    /**
     * @var \EonX\EasySwoole\Interfaces\ApplicationStateResetterInterface[]
     */
    private array $appStateResetters;

    public function __construct(iterable $appStateResetters)
    {
        $this->appStateResetters = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateResetters, ApplicationStateResetterInterface::class)
        );
    }

    public function __invoke(TerminateEvent $event): void
    {
        if (EasySwooleEnabledHelper::isNotEnabled($event->getRequest())) {
            return;
        }

        foreach ($this->appStateResetters as $appStateResetter) {
            $appStateResetter->resetState();
        }
    }
}
