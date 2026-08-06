<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithDoctrineManagersSanityCheckMiddlewareDisabled
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_async', [
        'messenger' => [
            'middleware' => [
                'doctrine_managers_sanity_check' => [
                    'enabled' => false,
                ],
            ],
        ],
    ]);
};
