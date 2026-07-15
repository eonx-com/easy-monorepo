<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessengerMiddlewareDisabledAndChildEnabled
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_async', [
        'messenger' => [
            'middleware' => [
                'enabled' => false,
                'doctrine_managers_clear' => [
                    'enabled' => true,
                ],
            ],
        ],
    ]);
};
