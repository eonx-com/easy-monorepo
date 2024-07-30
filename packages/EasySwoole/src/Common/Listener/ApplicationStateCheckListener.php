<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Checker\AppStateCheckerInterface;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ApplicationStateCheckListener extends AbstractTerminateListener
{
    /**
     * @var \EonX\EasySwoole\Common\Checker\AppStateCheckerInterface[]
     */
    private readonly array $appStateCheckers;

    /**
     * @param iterable<\EonX\EasySwoole\Common\Checker\AppStateCheckerInterface> $appStateCheckers
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
            if ($appStateChecker->isApplicationStateCompromised()) {
                $request->attributes->set(RequestAttribute::EasySwooleAppStateCompromised->value, true);

                // If at least one check says the state is compromised, it's enough
                $this->logger?->debug('Application state compromised, stopping checks', [
                    'appStateChecker' => $appStateChecker::class,
                ]);

                return;
            }
        }
    }
}
