<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Kernel;

use Bref\Bref;
use Bref\SymfonyBridge\BrefKernel;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Subscriber\InvocationLifecycleSubscriber;

/**
 * Base HTTP kernel for serverless (Bref / AWS Lambda) applications; serverless projects extend it.
 *
 * Its responsibility is to wire Bref's invocation lifecycle: inside a Lambda it subscribes
 * InvocationLifecycleSubscriber, which resets the Symfony HTTP handler after each invocation so
 * state does not leak between warm invocations.
 *
 * Trusted-proxy handling for the API Gateway context is intentionally NOT here: it must run after
 * Symfony's Kernel::preBoot() (which re-applies the app's trusted-proxy config on a cold start and
 * would override an earlier setting), so it lives in the high-priority kernel.request listener
 * TrustApiGatewayProxyRequestListener instead.
 */
abstract class AbstractServerlessHttpKernel extends BrefKernel
{
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        if (LambdaContextHelper::inLambda()) {
            Bref::events()->subscribe(new InvocationLifecycleSubscriber());
        }
    }
}
