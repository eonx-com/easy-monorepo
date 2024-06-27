<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Helper\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

abstract class AbstractTerminateListener
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
