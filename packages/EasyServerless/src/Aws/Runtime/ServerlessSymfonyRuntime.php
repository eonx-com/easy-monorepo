<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Runtime;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Helper\SecretsHelper;
use Symfony\Component\Runtime\SymfonyRuntime;

class ServerlessSymfonyRuntime extends SymfonyRuntime
{
    public function __construct(?array $options = null)
    {
        if (LambdaContextHelper::inRemoteLambda()) {
            SecretsHelper::load();
        }

        parent::__construct($options ?? []);
    }
}
