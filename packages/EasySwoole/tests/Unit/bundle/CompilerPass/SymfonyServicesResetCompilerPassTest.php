<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Bundle\CompilerPass;

use EonX\EasySwoole\Bundle\CompilerPass\SymfonyServicesResetCompilerPass;
use EonX\EasySwoole\Common\Resetter\SymfonyServicesAppStateResetter;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CheckAliasValidityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface;

final class SymfonyServicesResetCompilerPassTest extends AbstractUnitTestCase
{
    public function testItReplacesServicesResetterWithValidInterfaceAlias(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            SymfonyServicesAppStateResetter::class,
            new Definition(SymfonyServicesAppStateResetter::class)
        );
        $container->setDefinition(
            'services_resetter',
            new Definition(ServicesResetter::class, [new IteratorArgument([]), []])
        );
        $container->setAlias(ServicesResetterInterface::class, 'services_resetter');

        new SymfonyServicesResetCompilerPass()
            ->process($container);
        new CheckAliasValidityPass()
            ->process($container);

        $servicesResetter = $container->getDefinition('services_resetter');
        self::assertSame(SymfonyServicesAppStateResetter::class, $servicesResetter->getClass());
    }
}
