<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Common\Helper\EasySwooleEnabledHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;

abstract class AbstractRequestListener
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
