<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Helpers\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

abstract class AbstractExceptionEventListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (EasySwooleEnabledHelper::isNotEnabled($event->getRequest())) {
            return;
        }

        $this->doInvoke($event);
    }

    abstract protected function doInvoke(ExceptionEvent $event): void;
}
