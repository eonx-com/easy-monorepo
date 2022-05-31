<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Helpers\EasySwooleEnabledHelper;
use EonX\EasySwoole\Interfaces\ApplicationStateCheckerInterface;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateCheckListener
{
    /**
     * @var \EonX\EasySwoole\Interfaces\ApplicationStateCheckerInterface[]
     */
    private array $appStateCheckers;

    public function __construct(iterable $appStateCheckers)
    {
        $this->appStateCheckers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateCheckers, ApplicationStateCheckerInterface::class)
        );
    }

    public function __invoke(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        if (EasySwooleEnabledHelper::isNotEnabled($request)) {
            return;
        }

        foreach ($this->appStateCheckers as $appStateChecker) {
            if ($appStateChecker->isApplicationStateCompromised()) {
                $request->attributes->set(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, true);

                // If at least one check says the state is compromised, it's enough
                return;
            }
        }
    }
}
