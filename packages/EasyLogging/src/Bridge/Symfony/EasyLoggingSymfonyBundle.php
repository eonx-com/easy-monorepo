<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony;

use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\DefaultStreamHandlerPass;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\ReplaceChannelsDefinitionPass;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\SensitiveDataSanitizerCompilerPass;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\EasyLoggingExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyLoggingSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DefaultStreamHandlerPass())
            ->addCompilerPass(new ReplaceChannelsDefinitionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10)
            ->addCompilerPass(new SensitiveDataSanitizerCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyLoggingExtension();
    }
}
