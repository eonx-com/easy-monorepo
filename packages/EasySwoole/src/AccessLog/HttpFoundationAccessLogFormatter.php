<?php
declare(strict_types=1);

namespace EonX\EasySwoole\AccessLog;

use Carbon\CarbonImmutable;
use EonX\EasySwoole\Interfaces\HttpFoundationAccessLogFormatterInterface;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpFoundationAccessLogFormatter implements HttpFoundationAccessLogFormatterInterface
{
    public function formatAccessLog(Request $request, Response $response): string
    {
        $accessLog = \sprintf(
            '%s - "%s %s%s %s" %d "%s"',
            $request->getClientIp(),
            $request->getMethod(),
            $request->getPathInfo(),
            $request->getQueryString() ? '?' . $request->getQueryString() : null,
            $request->getProtocolVersion(),
            $response->getStatusCode(),
            $request->headers->get('user-agent', '<no_user_agent>'),
        );

        $startTime = $request->attributes->get(RequestAttributesInterface::EASY_SWOOLE_REQUEST_START_TIME);
        if ($startTime instanceof CarbonImmutable) {
            $accessLog .= \sprintf(
                ' - ReceivedAt: %s (%sms)',
                $startTime->toRfc3339String(),
                $startTime->diffInMilliseconds(CarbonImmutable::now('UTC'))
            );
        }

        $workerId = $request->attributes->get(RequestAttributesInterface::EASY_SWOOLE_WORKER_ID);
        if (\is_int($workerId)) {
            $accessLog .= \sprintf(' | SwooleWorker: %d', $workerId);
        }

        return $accessLog;
    }
}
