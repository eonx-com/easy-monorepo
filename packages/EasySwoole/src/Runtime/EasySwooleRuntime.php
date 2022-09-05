<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use EonX\EasyBugsnag\Interfaces\ValueOptionInterface as EasyBugsnagValueOptionInterface;
use EonX\EasyUtils\Helpers\EnvVarSubstitutionHelper;
use Swoole\Constant;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

use function Symfony\Component\String\u;

final class EasySwooleRuntime extends SymfonyRuntime
{
    /**
     * @throws \Exception
     */
    public function getRunner(?object $application): RunnerInterface
    {
        // Handle env var substitution
        foreach (EnvVarSubstitutionHelper::resolveVariables($_SERVER) as $name => $value) {
            $_SERVER[$name] = $value;
        }

        foreach (EnvVarSubstitutionHelper::resolveVariables($_ENV) as $name => $value) {
            $_ENV[$name] = $value;
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
                Constant::OPTION_REACTOR_NUM => \swoole_cpu_num() * 2,
                Constant::OPTION_WORKER_NUM => \swoole_cpu_num() * 2,
            ], $options['settings'] ?? []));

            // Bridge for eonx-com/easy-bugsnag to resolve request in CLI
            if (\interface_exists(EasyBugsnagValueOptionInterface::class)) {
                $_SERVER[EasyBugsnagValueOptionInterface::RESOLVE_REQUEST_IN_CLI] = true;
            }

            return new EasySwooleRunner($application, $options);
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
