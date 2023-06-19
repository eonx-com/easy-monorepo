<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use EonX\EasyBugsnag\Interfaces\ValueOptionInterface as EasyBugsnagValueOptionInterface;
use EonX\EasySwoole\Bridge\EasySchedule\EasyScheduleSwooleRunner;
use EonX\EasySwoole\Helpers\EnvVarHelper;
use EonX\EasySwoole\Helpers\FunctionHelper;
use EonX\EasySwoole\Helpers\SslCertificateHelper;
use Swoole\Constant;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

use function Symfony\Component\String\u;

final class EasySwooleRuntime extends SymfonyRuntime
{
    /**
     * @param mixed[]|null $options
     */
    public function __construct(?array $options = null)
    {
        // If dotenv_path is not set, set it to "envs/$env.env" if file exists
        if (isset($options['dotenv_path']) === false && isset($options['project_dir'])) {
            $envKey = $options['env_var_name'] ??= 'APP_ENV';
            $env = $options['env'] ??= $_SERVER[$envKey] ?? $_ENV[$envKey] ?? 'local';
            $envPath = \sprintf('envs/%s.env', \strtolower($env));
            $fullEnvPath = \sprintf('%s/%s', $options['project_dir'], $envPath);

            if (\is_file($fullEnvPath) && \is_readable($fullEnvPath)) {
                $options['dotenv_path'] = $envPath;
            }
        }

        parent::__construct($options ?? []);
    }

    /**
     * @throws \Exception
     */
    public function getRunner(?object $application): RunnerInterface
    {
        EnvVarHelper::loadEnvVars($this->options['json_secrets'] ?? null);

        if ($application instanceof Application && isset($this->options[EasyScheduleSwooleRunner::ENABLED])) {
            return new EasyScheduleSwooleRunner($application);
        }

        if ($application instanceof HttpKernelInterface) {
            $options = $this->options;
            $options['settings'] = $this->resolveSwooleSettings(\array_merge([
                // Process
                Constant::OPTION_DAEMONIZE => 0,
                Constant::OPTION_GROUP => 'www-data',
                Constant::OPTION_USER => 'www-data',
                // Static Handler
                Constant::OPTION_ENABLE_STATIC_HANDLER => true,
                Constant::OPTION_DOCUMENT_ROOT => '/var/www/public',
                // Processes number
                Constant::OPTION_REACTOR_NUM => FunctionHelper::countCpu() * 2,
                Constant::OPTION_WORKER_NUM => FunctionHelper::countCpu() * 2,
            ], $options['settings'] ?? []));

            // Bridge for eonx-com/easy-bugsnag to resolve request in CLI
            if (\interface_exists(EasyBugsnagValueOptionInterface::class)) {
                $_SERVER[EasyBugsnagValueOptionInterface::RESOLVE_REQUEST_IN_CLI] = true;
            }

            return new EasySwooleRunner($application, SslCertificateHelper::loadSslCertificates($options));
        }

        return parent::getRunner($application);
    }

    /**
     * Allows application to define individual Swoole settings using env variables.
     * Any option defined on \Swoole\Constant as a constant can be set using an env variable.
     * Simply replace the OPTION_ from the constant name with SWOOLE_SETTING_ in the env variable name.
     *
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    private function resolveSwooleSettings(array $settings): array
    {
        $reflection = new \ReflectionClass(Constant::class);
        $constants = $reflection->getConstants();

        foreach ($constants as $constant => $constantValue) {
            $constantName = u($constant);

            if ($constantName->startsWith('OPTION_')) {
                $constantName = $constantName
                    ->replace('OPTION_', 'SWOOLE_SETTING_')
                    ->toString();

                $value = $_SERVER[$constantName] ?? $_ENV[$constantName] ?? null;

                if ($value !== null && $value !== '') {
                    $settings[$constantValue] = $value;
                }
            }
        }

        return $settings;
    }
}
