<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallContext;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('firewall_config', FirewallConfig::class)
        ->arg('$name', 'my-firewall')
        ->arg('$userChecker', 'my-user-checker');

    $services->set('firewall_context', FirewallContext::class)
        ->arg('$listeners', [])
        ->arg('$exceptionListener', null)
        ->arg('$logoutListener', null)
        ->arg('$config', service('firewall_config'));
};
