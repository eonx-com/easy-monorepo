<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\ActivitySubjectDataResolver\Case7ActivitySubjectDataResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ActivitySubjectDataResolverInterface::class, Case7ActivitySubjectDataResolver::class);
};
