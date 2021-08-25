<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface as EasyApiTokenConstantsInterface;
use EonX\EasyApiToken\Bridge\Symfony\EasyApiTokenSymfonyBundle;
use EonX\EasyLogging\Bridge\Symfony\EasyLoggingSymfonyBundle;
use EonX\EasySecurity\Bridge\Symfony\EasySecuritySymfonyBundle;
use EonX\EasySecurity\Tests\Stubs\ApiTokenDecoderProviderStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $configs;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @param null|string[] $configs
     */
    public function __construct(string $environment, bool $debug, ?array $configs = null, ?Request $request = null)
    {
        $this->configs = $configs ?? [];
        $this->request = $request;

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        // ApiTokenDecoderProvider
        $container
            ->setDefinition(ApiTokenDecoderProviderStub::class, new Definition(ApiTokenDecoderProviderStub::class))
            ->addTag(EasyApiTokenConstantsInterface::TAG_DECODER_PROVIDER);

        // RequestStack
        $requestStackDef = new Definition(RequestStack::class);

        if ($this->request !== null) {
            $requestStackDef->addMethodCall('push', [$this->request]);
        }

        $container->setDefinition(RequestStack::class, $requestStackDef);

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiTokenSymfonyBundle();
        yield new EasyLoggingSymfonyBundle();
        yield new EasySecuritySymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../Fixtures/config/default.yaml');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
