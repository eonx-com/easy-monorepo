<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Logging\Exception\SwooleDdException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class SwooleDdListener extends AbstractExceptionListener
{
    protected function doInvoke(ExceptionEvent $event): void
    {
        // Simply return empty 500 response as swoole_dd already echo the content
        // and, it will be added to the response by EasySwooleRunner
        if ($event->getThrowable() instanceof SwooleDdException) {
            $event->setResponse(new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
