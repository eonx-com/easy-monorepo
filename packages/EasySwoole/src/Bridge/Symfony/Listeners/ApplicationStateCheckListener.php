<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Interfaces\AppStateCheckerInterface;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateCheckListener extends AbstractTerminateEventListener
{
    /**
     * @var \EonX\EasySwoole\Interfaces\AppStateCheckerInterface[]
     */
    private array $appStateCheckers;

    /**
     * @param iterable<\EonX\EasySwoole\Interfaces\AppStateCheckerInterface> $appStateCheckers
     */
    public function __construct(iterable $appStateCheckers)
    {
        $this->appStateCheckers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateCheckers, AppStateCheckerInterface::class)
        );
    }

    protected function doInvoke(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->appStateCheckers as $appStateChecker) {
            if ($appStateChecker->isApplicationStateCompromised()) {
                $request->attributes->set(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, true);

                // If at least one check says the state is compromised, it's enough
                return;
            }
        }
    }
}
