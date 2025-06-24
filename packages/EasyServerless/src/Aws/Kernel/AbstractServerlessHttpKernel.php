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
        // Symfony requires $_SERVER['REMOTE_ADDR'] to be set in order to set trusted proxies properly
        // Because we are within the Lambda context behind ApiGateway, we can safely trust the one from the request
        if (LambdaContextHelper::inRemoteLambda()) {
            $_SERVER['REMOTE_ADDR'] = $request->server->get('REMOTE_ADDR', '127.0.0.1');

            Request::setTrustedProxies(
                ['REMOTE_ADDR'],
                Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
            );
        }

        return parent::handle($request, $type, $catch);
    }
}
