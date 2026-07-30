<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Kernel;

use Bref\Bref;
use Bref\SymfonyBridge\BrefKernel;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Subscriber\InvocationLifecycleSubscriber;

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
