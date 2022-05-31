<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

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
                Constant::OPTION_WORKER_NUM => \swoole_cpu_num() * 2,
            ], $options['settings'] ?? []);

            return new EasySwooleRunner($application, $options);
        }

        return parent::getRunner($application);
    }
}
