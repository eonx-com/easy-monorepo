<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Common\Resolver\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Tests\Fixture\App\ActivitySubjectDataResolver\CustomActivitySubjectDataResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ActivitySubjectDataResolverInterface::class, CustomActivitySubjectDataResolver::class);
};
