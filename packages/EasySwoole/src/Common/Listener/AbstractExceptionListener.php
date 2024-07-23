<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Helper\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

abstract class AbstractExceptionListener
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
