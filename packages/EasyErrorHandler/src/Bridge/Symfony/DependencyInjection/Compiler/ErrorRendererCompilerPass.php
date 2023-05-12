<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Bridge\Symfony\ErrorRenderer\TranslateInternalErrorMessageErrorRenderer;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ErrorRendererCompilerPass implements CompilerPassInterface
{
    private const ERROR_RENDERER_ID = 'error_renderer';

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::ERROR_RENDERER_ID) === false) {
            return;
        }

        $def = (new Definition(TranslateInternalErrorMessageErrorRenderer::class))
            ->setArgument('$errorDetailsResolver', new Reference(ErrorDetailsResolverInterface::class))
            ->setArgument('$decorated', new Reference('.inner'))
            ->setDecoratedService(self::ERROR_RENDERER_ID);

        $container->setDefinition(TranslateInternalErrorMessageErrorRenderer::class, $def);
    }
}
