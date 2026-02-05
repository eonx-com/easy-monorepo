<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Application;

use EonX\EasyServerless\Aws\Console\Factory\OutputFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class exists to allow applications which has their own console application implementation
 * to extend it and be able to benefit from the features of this package.
 */
abstract class AbstractServerlessConsoleApplication extends Application
{
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        $kernel = $this->getKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        if ($container->has(OutputFactoryInterface::class)) {
            $output = $container->get(OutputFactoryInterface::class)->create($output);
        }

        return parent::run($input, $output);
    }
}
