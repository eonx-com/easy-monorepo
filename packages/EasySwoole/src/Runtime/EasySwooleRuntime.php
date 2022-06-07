<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use EonX\EasyBugsnag\Interfaces\ValueOptionInterface as EasyBugsnagValueOptionInterface;
use Swoole\Constant;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

final class EasySwooleRuntime extends SymfonyRuntime
{
    /**
     * @throws \Exception
     */
    public function getRunner(?object $application): RunnerInterface
    {
        if ($application instanceof HttpKernelInterface) {
            $options = $this->options;
            $options['settings'] = \array_merge([
                // Process
                Constant::OPTION_DAEMONIZE => 0,
                Constant::OPTION_GROUP => 'www-data',
                Constant::OPTION_USER => 'www-data',
                // Static Handler
                Constant::OPTION_ENABLE_STATIC_HANDLER => true,
                Constant::OPTION_DOCUMENT_ROOT => '/var/www/public',
                // Workers number
                Constant::OPTION_WORKER_NUM => \swoole_cpu_num() * 2,
            ], $options['settings'] ?? []);

            // Bridge for eonx-com/easy-bugsnag to resolve request in CLI
            if (\interface_exists(EasyBugsnagValueOptionInterface::class)) {
                $_SERVER[EasyBugsnagValueOptionInterface::RESOLVE_REQUEST_IN_CLI] = true;
            }

            return new EasySwooleRunner($application, $options);
        }

        return parent::getRunner($application);
    }
}
