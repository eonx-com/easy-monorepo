<?php
declare(strict_types=1);

namespace EonX\EasyTest\Bundle;

use EonX\EasyTest\Bundle\CompilerPass\RegisterTraceableErrorHandlerStubCompilerPass;
use Monolog\LogRecord;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyTestBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new RegisterTraceableErrorHandlerStubCompilerPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (\class_exists(LogRecord::class)) {
            $container->import('config/services.php');
        }
    }
}
