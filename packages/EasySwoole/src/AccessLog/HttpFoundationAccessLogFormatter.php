<?php
declare(strict_types=1);

namespace EonX\EasySwoole\AccessLog;

use EonX\EasySwoole\Interfaces\HttpFoundationAccessLogFormatterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpFoundationAccessLogFormatter implements HttpFoundationAccessLogFormatterInterface
{
    public function formatAccessLog(Request $request, Response $response): string
    {
        return \sprintf(
            '%s - "%s %s%s %s" %d "%s"',
            $request->getClientIp(),
            $request->getMethod(),
            $request->getPathInfo(),
            $request->getQueryString() ? '?' . $request->getQueryString() : null,
            $request->getProtocolVersion(),
            $response->getStatusCode(),
            $request->headers->get('user-agent', '<no_user_agent>')
        );
    }
}
