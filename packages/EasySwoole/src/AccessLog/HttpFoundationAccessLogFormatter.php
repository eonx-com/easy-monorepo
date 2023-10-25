<?php
declare(strict_types=1);

namespace EonX\EasySwoole\AccessLog;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use EonX\EasySwoole\Interfaces\HttpFoundationAccessLogFormatterInterface;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpFoundationAccessLogFormatter implements HttpFoundationAccessLogFormatterInterface
{
    public function formatAccessLog(Request $request, Response $response): string
    {
        $durationInMs = '';
        $startTime = $request->attributes->get(RequestAttributesInterface::EASY_SWOOLE_REQUEST_START_TIME);

        if ($startTime instanceof CarbonImmutable) {
            $durationInMs = \sprintf(
                ' - ReceivedAt: %s (%sms)',
                $startTime->format(DateTimeInterface::RFC3339),
                $startTime->diffInMilliseconds(CarbonImmutable::now('UTC'))
            );
        }

        return \sprintf(
            '%s - "%s %s%s %s" %d "%s"%s',
            $request->getClientIp(),
            $request->getMethod(),
            $request->getPathInfo(),
            $request->getQueryString() ? '?' . $request->getQueryString() : null,
            $request->getProtocolVersion(),
            $response->getStatusCode(),
            $request->headers->get('user-agent', '<no_user_agent>'),
            $durationInMs
        );
    }
}
