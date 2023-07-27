<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Helpers\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

abstract class AbstractTerminateEventListener
{
    public function __invoke(TerminateEvent $event): void
    {
        if (EasySwooleEnabledHelper::isNotEnabled($event->getRequest())) {
            return;
        }

        $this->doInvoke($event);
    }

    abstract protected function doInvoke(TerminateEvent $event): void;
}
