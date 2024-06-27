<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\CompilerPass;

use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Bundle\Enum\ConfigTag;
use EonX\EasySwoole\EasyBatch\Resetter\BatchProcessorResetter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ResetEasyBatchProcessorCompilerPass implements CompilerPassInterface
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
            ->addTag(ConfigTag::AppStateResetter->value);

        $container->setDefinition(BatchProcessorResetter::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::ResetEasyBatchProcessor->value)
            && $container->getParameter(ConfigParam::ResetEasyBatchProcessor->value);
    }
}
