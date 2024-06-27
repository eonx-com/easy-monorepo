<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Listener;

use EonX\EasySwoole\Logging\Formatter\HttpFoundationAccessLogFormatterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class AccessLogListener extends AbstractTerminateListener
{
    /**
     * @param string[] $doNotLogPaths
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpFoundationAccessLogFormatterInterface $accessLogFormatter,
        private readonly array $doNotLogPaths,
    ) {
    }

    protected function doInvoke(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        if (\in_array($request->getPathInfo(), $this->doNotLogPaths, true)) {
            return;
        }

        $this->logger->debug(
            $this->accessLogFormatter->formatAccessLog($request, $event->getResponse())
        );
    }
}
