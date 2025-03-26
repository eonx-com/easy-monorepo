<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\HttpKernel;

use Bref\SymfonyBridge\BrefKernel;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class AbstractServerlessHttpKernel extends BrefKernel
{
    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true,
    ): Response {
        // Symfony requires $_SERVER['REMOTE_ADDR'] to be set in order to set trusted proxies properly
        // Because we are within the Lambda context behind ApiGateway, we can safely trust the one from the request
        if (LambdaContextHelper::inRemoteLambda() && isset($_SERVER['REMOTE_ADDR']) === false) {
            $_SERVER['REMOTE_ADDR'] = $request->server->get('REMOTE_ADDR');

            Request::setTrustedProxies(
                ['REMOTE_ADDR'],
                Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
            );
        }

        return parent::handle($request, $type, $catch);
    }
}
