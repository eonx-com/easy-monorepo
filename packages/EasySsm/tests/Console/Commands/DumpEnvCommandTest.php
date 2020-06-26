<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Console\Commands;

use EonX\EasySsm\Tests\AbstractTestCase;

final class DumpEnvCommandTest extends AbstractTestCase
{
    public function testDumpEnvCommand(): void
    {
        $_SERVER['server_test'] = 'value';
        $_ENV['env_test'] = 'value';

        $options = ['-i' => ['server_test'], '-e' => ['env_test']];

        $this->executeCommand('dump-env', null, [
            __DIR__ . '/../../../config/console_loader.yaml',
        ], $options);

        $filename = '.env.local.php';
        $contents = (string)\file_get_contents($filename);

        self::assertTrue(\is_file($filename));
        self::assertStringContainsString('server_test', $contents);
        self::assertStringNotContainsString('env_test', $contents);
    }
}
