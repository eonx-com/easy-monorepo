<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Listener;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Trusts only the API Gateway source IP (REMOTE_ADDR) as the client IP, on each main request.
 *
 * Runs as a high-priority kernel.request listener (before anything reads the client IP), NOT from
 * the kernel's handle(): on a cold Lambda start Kernel::preBoot() re-applies the app's
 * trusted_proxies/trusted_headers and would override an earlier setting, and preBoot runs before any
 * kernel.request listener.
 *
 * X-Forwarded-For is deliberately NOT trusted: API Gateway forwards a caller-supplied value, so
 * trusting it would let anyone spoof getClientIp(). (Assumes API Gateway; behind an ALB the client
 * IP is in X-Forwarded-For instead, so this policy would be wrong there.)
 */
#[AsEventListener(event: RequestEvent::class, priority: 50_000)]
final class TrustApiGatewayProxyRequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        // Main request only: trusted proxies are a global static, and a sub-request's synthetic
        // server bag could otherwise clobber the real source IP with the 127.0.0.1 fallback
        if ($event->isMainRequest() === false || LambdaContextHelper::inRemoteLambda() === false) {
            return;
        }

        // Symfony resolves the 'REMOTE_ADDR' trusted proxy against the global $_SERVER value
        $_SERVER['REMOTE_ADDR'] = $event->getRequest()->server->get('REMOTE_ADDR', '127.0.0.1');

        Request::setTrustedProxies(
            ['REMOTE_ADDR'],
            Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    }
}
