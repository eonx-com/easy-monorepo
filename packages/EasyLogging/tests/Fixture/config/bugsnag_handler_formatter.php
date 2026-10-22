<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use Monolog\Formatter\LineFormatter;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of bugsnag_handler.php: the recipe documented in
    // docs/symfony/install.md to customise the formatter (application definitions win over the bundle ones)
    $containerConfigurator->services()
        ->set(BugsnagMonologHandler::class)
        ->autowire()
        ->arg('$level', param(ConfigParam::BugsnagHandlerLevel->value))
        ->call('setFormatter', [inline_service(LineFormatter::class)->args(['%%message%%'])]);
};
