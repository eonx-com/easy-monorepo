<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Helpers\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;

abstract class AbstractRequestEventListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (EasySwooleEnabledHelper::isNotEnabled($event->getRequest())) {
            return;
        }

        $this->doInvoke($event);
    }

    abstract protected function doInvoke(RequestEvent $event): void;
}
