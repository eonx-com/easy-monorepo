<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\EasyUtils\Exceptions\EasyUtilsNotInstalledException;
use EonX\EasyBugsnag\Bridge\EasyUtils\SensitiveDataSanitizerConfigurator;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SensitiveDataCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    public const SANITIZER_ID = SensitiveDataSanitizerInterface::class;

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        if (\interface_exists(self::SANITIZER_ID) === false || $container->has(self::SANITIZER_ID) === false) {
            throw new EasyUtilsNotInstalledException(
                'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                and its bundle must be enabled'
            );
        }

        $def = new Definition(SensitiveDataSanitizerConfigurator::class, [
            '$sensitiveDataSanitizer' => new Reference(SensitiveDataSanitizerInterface::class),
        ]);
        $def->setAutoconfigured(true);

        $container->setDefinition(SensitiveDataSanitizerConfigurator::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(BridgeConstantsInterface::PARAM_SENSITIVE_DATA_SANITIZER_ENABLED)
            && $container->getParameter(BridgeConstantsInterface::PARAM_SENSITIVE_DATA_SANITIZER_ENABLED);
    }
}
