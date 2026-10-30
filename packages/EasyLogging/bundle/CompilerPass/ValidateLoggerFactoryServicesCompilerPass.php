<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Configurator\LoggerConfiguratorInterface;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * The LoggerFactory config providers are only used by the Laravel integration: in Symfony such services would be
 * silently ignored, so the container compilation fails instead.
 */
final class ValidateLoggerFactoryServicesCompilerPass implements CompilerPassInterface
{
    private const string MONOLOG_PROCESSOR_TAG = 'monolog.processor';

    private const array UNSUPPORTED_INTERFACES = [
        HandlerConfigProviderInterface::class => 'declare the handler under "monolog.handlers"',
        LoggerConfiguratorInterface::class => 'configure the logger through symfony/monolog-bundle',
        ProcessorConfigProviderInterface::class => 'autoconfigure it or add the "monolog.processor" tag',
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract() || $definition->isSynthetic()) {
                continue;
            }

            /** @var string|null $class */
            $class = $container->getParameterBag()
                ->resolveValue($definition->getClass() ?? $id);
            $reflectionClass = \is_string($class) ? $container->getReflectionClass($class, false) : null;

            if ($reflectionClass === null) {
                continue;
            }

            foreach (self::UNSUPPORTED_INTERFACES as $interface => $solution) {
                if ($reflectionClass->implementsInterface($interface) === false) {
                    continue;
                }

                if (
                    $interface === ProcessorConfigProviderInterface::class
                    && $definition->hasTag(self::MONOLOG_PROCESSOR_TAG)
                ) {
                    continue;
                }

                throw new LogicException(\sprintf(
                    'Service "%s" implements "%s", which is used by the Laravel integration only and is ignored '
                    . 'in Symfony: %s.',
                    $id,
                    $interface,
                    $solution
                ));
            }
        }
    }
}
