<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Kernel;

use EonX\EasyApiToken\Bundle\EasyApiTokenBundle;
use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasySecurity\Bundle\EasySecurityBundle;
use EonX\EasySecurity\Tests\Stub\Provider\ApiTokenDecoderProviderStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private readonly array $configs;

    /**
     * @param string[]|null $configs
     */
    public function __construct(
        string $environment,
        bool $debug,
        ?array $configs = null,
        private readonly ?Request $request = null,
    ) {
        $this->configs = $configs ?? [];

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        // ApiTokenDecoderProvider
        $container
            ->setDefinition(ApiTokenDecoderProviderStub::class, new Definition(ApiTokenDecoderProviderStub::class))
            ->addTag(ConfigTag::DecoderProvider->value);

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
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiTokenBundle();
        yield new EasyLoggingBundle();
        yield new EasySecurityBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
