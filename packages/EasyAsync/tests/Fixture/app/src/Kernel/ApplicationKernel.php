<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Fixture\App\Kernel;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class ApplicationKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->import($this->getProjectDir() . '/config/{packages}/*.php');
        $container->import($this->getProjectDir() . '/config/{packages}/' . $this->environment . '/*.php');
        $container->import($this->getProjectDir() . '/config/{parameters}.php');
        $container->import($this->getProjectDir() . '/config/{parameters}_' . $this->environment . '.php');
        $container->import($this->getProjectDir() . '/config/{services}.php');
        $container->import($this->getProjectDir() . '/config/{services}_' . $this->environment . '.php');
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/{routes}/*.php');
        $routes->import($this->getProjectDir() . '/config/{routes}.php');
    }
}
