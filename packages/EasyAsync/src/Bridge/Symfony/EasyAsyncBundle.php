<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony;

use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\Compiler\AddBatchMiddlewareToMessengerBusesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyAsyncBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddBatchMiddlewareToMessengerBusesPass());
    }
}
