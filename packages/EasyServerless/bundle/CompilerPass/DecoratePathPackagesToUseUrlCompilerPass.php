<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\CompilerPass;

use EonX\EasyServerless\Asset\Package\PrefixedUrlPackage;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DecoratePathPackagesToUseUrlCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter(ConfigParam::AssetsSeparateDomainEnabled->value) === false) {
            return;
        }

        $packages = $container->findTaggedServiceIds('assets.package');

        foreach ($packages as $id => $tag) {
            $packageDefinition = $container->getDefinition($id);
            $packageClass = $packageDefinition->getClass();

            if (\is_string($packageClass) === false || \is_a($packageClass, UrlPackage::class, true)) {
                continue;
            }

            $decoratorDefinition = (new Definition(PrefixedUrlPackage::class))
                ->setDecoratedService($id)
                ->setArgument('$assetsUrl', $container->getParameter(ConfigParam::AssetsSeparateDomainUrl->value))
                ->setArgument('$decorated', new Reference('.inner'));

            $container->setDefinition(\sprintf('%s_url_decorator', $id), $decoratorDefinition);
        }
    }
}
