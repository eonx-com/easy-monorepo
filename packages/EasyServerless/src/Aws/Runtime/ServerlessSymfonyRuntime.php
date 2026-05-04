<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Runtime;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Helper\SecretsHelper;
use Symfony\Component\Runtime\SymfonyRuntime;

class ServerlessSymfonyRuntime extends SymfonyRuntime
{
    public const OPTION_AWS_HELPER_VERBOSE = 'easy_serverless.aws_helper_verbose';

    public function __construct(?array $options = null)
    {
        if (LambdaContextHelper::inRemoteLambda()) {
            SecretsHelper::setVerbose($options[self::OPTION_AWS_HELPER_VERBOSE] ?? false);
            SecretsHelper::load();
        }

        parent::__construct($options ?? []);
    }
}
