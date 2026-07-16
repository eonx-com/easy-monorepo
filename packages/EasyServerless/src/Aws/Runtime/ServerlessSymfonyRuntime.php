<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Runtime;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\Helper\SecretsHelper;
use Symfony\Component\Runtime\SymfonyRuntime;

class ServerlessSymfonyRuntime extends SymfonyRuntime
{
    public const string OPTION_AWS_HELPER_VERBOSE = 'easy_serverless.aws_helper_verbose';

    /**
     * @param array{
     *   self::OPTION_AWS_HELPER_VERBOSE?: ?bool,
     *   debug?: ?bool,
     *   env?: ?string,
     *   disable_dotenv?: ?bool,
     *   project_dir?: ?string,
     *   prod_envs?: ?string[],
     *   dotenv_path?: ?string,
     *   test_envs?: ?string[],
     *   use_putenv?: ?bool,
     *   runtimes?: ?array,
     *   error_handler?: string|false,
     *   env_var_name?: string,
     *   debug_var_name?: string,
     *   project_dir_var?: string|false,
     *   dotenv_overload?: ?bool,
     *   dotenv_extra_paths?: ?string[],
     *   worker_loop_max?: int, // Use 0 or a negative integer to never restart the worker. Default: 500
     * } $options
     */
    public function __construct(array $options = [])
    {
        if (LambdaContextHelper::inRemoteLambda()) {
            SecretsHelper::setVerbose($options[self::OPTION_AWS_HELPER_VERBOSE] ?? false);
            SecretsHelper::load();
        }

        parent::__construct($options);
    }
}
