<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Listener;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Configures Symfony's trusted-proxy resolution for the Lambda + API Gateway context, on every
 * request.
 *
 * This runs on kernel.request rather than from the kernel's handle() for a reason: on a COLD Lambda
 * start Symfony's Kernel::preBoot() applies the app's framework.trusted_proxies/trusted_headers
 * (which often still trusts X-Forwarded-For) and would override a setting made before it. preBoot
 * always runs before any kernel.request listener, so re-applying the policy here holds on both cold
 * and warm invocations. The high priority makes it run before anything that resolves the client IP
 * (security context, rate limiting, logging).
 *
 * Symfony needs $_SERVER['REMOTE_ADDR'] set to resolve the 'REMOTE_ADDR' trusted proxy. On Lambda
 * behind API Gateway the platform provides the real client IP as REMOTE_ADDR (the API Gateway
 * source IP), so that is what we trust.
 *
 * We deliberately do NOT trust X-Forwarded-For for the client IP: API Gateway forwards a
 * caller-supplied X-Forwarded-For header, so trusting it would let anyone spoof getClientIp() to
 * any value by prepending an entry (defeating IP-based rate limiting, allow-lists or audit
 * logging). The client IP is taken from the platform-set REMOTE_ADDR, which a caller cannot forge.
 * X-Forwarded-Proto/Port stay trusted so HTTPS-aware URL and port generation remains correct.
 *
 * NOTE: this assumes an API Gateway integration. Behind an ALB the real client IP arrives in
 * X-Forwarded-For (set by the load balancer) while REMOTE_ADDR is the balancer's address, so this
 * policy would not be correct there.
 *
 * The priority is well above the app and framework kernel.request listeners that resolve the client
 * IP (the highest observed across our services sits around 20000), so the policy is set first.
 */
#[AsEventListener(event: RequestEvent::class, priority: 50_000)]
final class TrustApiGatewayProxyRequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (LambdaContextHelper::inRemoteLambda() === false) {
            return;
        }

        $this->trustApiGatewayProxy($event->getRequest());
    }

    public function trustApiGatewayProxy(Request $request): void
    {
        $_SERVER['REMOTE_ADDR'] = $request->server->get('REMOTE_ADDR', '127.0.0.1');

        Request::setTrustedProxies(
            ['REMOTE_ADDR'],
            Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    }
}
