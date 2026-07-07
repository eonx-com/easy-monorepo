<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class SensitiveDataSanitizerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        if (
            \interface_exists(SensitiveDataSanitizerInterface::class) === false
            || $container->has(SensitiveDataSanitizerInterface::class) === false
        ) {
            throw new LogicException(
                'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                and its bundle must be enabled'
            );
        }

        $def = (new Definition(SensitiveDataSanitizerProcessor::class, [
            '$sensitiveDataSanitizer' => new Reference(SensitiveDataSanitizerInterface::class),
        ]))
            ->addTag(ConfigTag::ProcessorConfigProvider->value)
            // Registered for symfony/monolog-bundle as well; the lowest priority runs it last, after other
            // processors have enriched the record. Ignored when symfony/monolog-bundle is not installed.
            ->addTag('monolog.processor', [
                'priority' => -9999,
            ])
            ->setAutoconfigured(true);

        $container->setDefinition(SensitiveDataSanitizerProcessor::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::SensitiveDataSanitizerEnabled->value)
            && $container->getParameter(ConfigParam::SensitiveDataSanitizerEnabled->value);
    }
}
