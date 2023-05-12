<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use ApiPlatform\Core\Bridge\Symfony\Validator\EventListener\ValidationExceptionListener;
use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use EonX\EasyLogging\Bridge\Symfony\EasyLoggingSymfonyBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var mixed[]
     */
    private readonly array $configs;

    /**
     * @param null|mixed[] $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(Client::class, new Definition(BugsnagClientStub::class));
        $container->setDefinition(TranslatorInterface::class, new Definition(TranslatorStub::class));
        $container->setDefinition(ValidationExceptionListener::class, new Definition(SerializerStub::class, [
            new Definition(SerializerStub::class),
            [],
        ]));

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyLoggingSymfonyBundle();
        yield new EasyErrorHandlerSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
