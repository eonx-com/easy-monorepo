<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\Coverage\Application\EasyTestApplication;
use EonX\EasyTest\Coverage\Locator\CoverageResolverLocator;
use EonX\EasyTest\Coverage\Resolver\CloverCoverageResolver;
use EonX\EasyTest\Coverage\Resolver\TextCoverageResolver;
use Symfony\Component\Console\Command\Command;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services->instanceof(Command::class)
        ->tag('app.console_command');

    $services->load('EonX\\EasyTest\\Coverage\\', __DIR__ . '/../src/Coverage/*')
        ->exclude([
            '../src/Coverage/Kernel/*',
            '../src/Coverage/ValueObject/*',
        ]);

    $services->set(EasyTestApplication::class)
        ->arg('$commands', tagged_iterator('app.console_command'));

    $services->set(CloverCoverageResolver::class)
        ->tag('app.easy_test.coverage_resolver', ['key' => 'clover']);

    $services->set(TextCoverageResolver::class)
        ->tag('app.easy_test.coverage_resolver', ['key' => 'txt']);

    $services->set(CoverageResolverLocator::class)
        ->arg('$coverageResolvers', tagged_locator('app.easy_test.coverage_resolver', 'key'));
};
