<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Caching\Helper;

use EonX\EasySwoole\Logging\Helper\OutputHelper;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

final class AppCacheWarmupHelper
{
    public static function warmupCache(?object $application): void
    {
        try {
            self::doWarmupCache($application);
        } catch (Throwable $throwable) {
            OutputHelper::writeln(\sprintf('Cache warmup failed: %s', $throwable->getMessage()));
        }
    }

    /**
     * @throws \Exception
     */
    private static function doWarmupCache(?object $application): void
    {
        if (\interface_exists(KernelInterface::class)
            && $application instanceof KernelInterface
            && \class_exists(ConsoleApplication::class)
        ) {
            $consoleApp = new ConsoleApplication($application);
            $consoleApp->setAutoExit(false);

            $consoleApp->run(new ArrayInput(['command' => 'cache:warmup']));
        }
    }
}
