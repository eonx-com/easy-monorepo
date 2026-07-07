<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated Will be removed in 7.0. The SensitiveDataSanitizerProcessor is now registered via
 *             config/sensitive_data_sanitizer.php and tagged through autoconfiguration (for the EasyLogging
 *             LoggerFactory) and the #[AsMonologProcessor] attribute (for symfony/monolog-bundle).
 */
final class SensitiveDataSanitizerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // No-op: registration moved to config/sensitive_data_sanitizer.php

    }
}
