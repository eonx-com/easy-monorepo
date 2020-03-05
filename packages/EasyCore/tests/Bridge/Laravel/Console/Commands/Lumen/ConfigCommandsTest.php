<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel\Console\Commands\Lumen;

use EonX\EasyCore\Tests\AbstractTestCase;
use Laravel\Lumen\Console\Kernel;

final class ConfigCommandsTest extends AbstractTestCase
{
    public function testCacheAndClearConfig(): void
    {
        /** @var \Laravel\Lumen\Application $app */
        $app = require __DIR__ . '/../../../fixtures/commands/cache_config/bootstrap/app.php';

        $kernel = new Kernel($app);
        $kernel->call('config:cache');

        $expectedPath = $app->storagePath('cached_config.php');
        $expectedConfig = ['database' => ['connection' => 'not-cached-connection']];

        self::assertFileExists($expectedPath);
        self::assertEquals(require $expectedPath, $expectedConfig);

        $kernel->call('config:clear');

        self::assertFileNotExists($expectedPath);
    }
}
