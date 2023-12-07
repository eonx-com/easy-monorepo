<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyErrorHandlerPublicPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (\interface_exists(ErrorHandlerInterface::class) === false) {
            return;
        }

        if ($container->hasAlias(ErrorHandlerInterface::class)) {
            $container->getAlias(ErrorHandlerInterface::class)
                ->setPublic(true);
        }

        if ($container->hasDefinition(ErrorHandlerInterface::class)) {
            $container->getDefinition(ErrorHandlerInterface::class)
                ->setPublic(true);
        }
    }
}
