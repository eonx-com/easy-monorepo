<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Dotenv;

use EonX\EasySsm\Dotenv\SsmDotenv;
use EonX\EasySsm\HttpKernel\EasySsmKernel;
use EonX\EasySsm\Tests\AbstractTestCase;

final class SsmDotenvTest extends AbstractTestCase
{
    public function testLoadEnv(): void
    {
        $kernel = new EasySsmKernel([
            __DIR__ . '/../../config/dotenv_loader.yaml',
            __DIR__ . '/../Fixtures/Config/stub_ssm_client.yaml',
        ]);

        (new SsmDotenv($kernel))->loadEnv();

        self::assertEquals('value', $_ENV['param']);
        self::assertEquals('value', $_SERVER['param']);
        self::assertEquals('value', \getenv('param'));
    }
}
