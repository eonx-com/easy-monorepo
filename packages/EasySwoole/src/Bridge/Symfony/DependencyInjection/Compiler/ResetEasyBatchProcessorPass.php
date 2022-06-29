<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyBatch\Processors\BatchProcessor;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\EasyBatch\BatchProcessorResetter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ResetEasyBatchProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false
            || \class_exists(BatchProcessor::class) === false
            || $container->has(BatchProcessor::class) === false) {
            return;
        }

        $def = (new Definition(BatchProcessorResetter::class))
            ->setArgument('$batchProcessor', new Reference(BatchProcessor::class))
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_RESETTER);

        $container->setDefinition(BatchProcessorResetter::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(BridgeConstantsInterface::PARAM_RESET_EASY_BATCH_PROCESSOR)
            && $container->getParameter(BridgeConstantsInterface::PARAM_RESET_EASY_BATCH_PROCESSOR);
    }
}
