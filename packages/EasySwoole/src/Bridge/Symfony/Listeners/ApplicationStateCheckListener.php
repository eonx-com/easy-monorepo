<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Interfaces\AppStateCheckerInterface;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Psr\Log\LoggerInterface;
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
    public function __construct(
        iterable $appStateCheckers,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->appStateCheckers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($appStateCheckers, AppStateCheckerInterface::class)
        );
    }

    protected function doInvoke(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->appStateCheckers as $appStateChecker) {
            $this->logger?->debug(\sprintf('Checking application state with "%s"', $appStateChecker::class));

            if ($appStateChecker->isApplicationStateCompromised()) {
                $request->attributes->set(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, true);

                // If at least one check says the state is compromised, it's enough
                $this->logger?->debug('Application state compromised, stopping checks');

                return;
            }
        }
    }
}
