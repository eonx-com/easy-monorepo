<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Kernel;

use Bref\Bref;
use Bref\SymfonyBridge\BrefKernel;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Subscriber\InvocationLifecycleSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class AbstractServerlessHttpKernel extends BrefKernel
{
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        if (LambdaContextHelper::inLambda()) {
            Bref::events()->subscribe(new InvocationLifecycleSubscriber());
        }
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true,
    ): Response {
        if (LambdaContextHelper::inRemoteLambda()) {
            $this->trustApiGatewayProxy($request);
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * Configures Symfony's trusted-proxy resolution for the Lambda + API Gateway context.
     *
     * Symfony needs $_SERVER['REMOTE_ADDR'] set to resolve trusted proxies. On Lambda the platform
     * provides the real client IP as REMOTE_ADDR (the API Gateway source IP), so that is what we trust.
     *
     * We deliberately do NOT trust X-Forwarded-For for the client IP: API Gateway forwards a
     * caller-supplied X-Forwarded-For header, so trusting it would let anyone spoof getClientIp()
     * to any value by prepending an entry to that header (defeating any IP-based rate limiting,
     * allow-list or audit logging). The client IP is instead taken from the platform-set
     * REMOTE_ADDR, which a caller cannot forge. X-Forwarded-Proto/Port are still trusted so
     * HTTPS-aware URL and port generation stays correct.
     */
    protected function trustApiGatewayProxy(Request $request): void
    {
        $_SERVER['REMOTE_ADDR'] = $request->server->get('REMOTE_ADDR', '127.0.0.1');

        Request::setTrustedProxies(
            ['REMOTE_ADDR'],
            Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    }
}
