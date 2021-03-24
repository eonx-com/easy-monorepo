<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony;

use EonX\EasySchedule\Bridge\Symfony\DependencyInjection\Compiler\SchedulePass;
use EonX\EasySchedule\Bridge\Symfony\DependencyInjection\EasyScheduleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyScheduleSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SchedulePass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyScheduleExtension();
    }
}
