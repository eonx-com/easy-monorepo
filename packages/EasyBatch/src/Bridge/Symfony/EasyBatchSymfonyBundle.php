<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony;

use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler\SetEncryptorOnBatchItemTransformerCompilerPass;
use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\EasyBatchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyBatchSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetEncryptorOnBatchItemTransformerCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyBatchExtension();
    }
}
