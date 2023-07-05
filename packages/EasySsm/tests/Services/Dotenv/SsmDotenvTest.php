<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Dotenv;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Aws\SsmPathResolver;
use EonX\EasySsm\Services\Dotenv\Data\EnvData;
use EonX\EasySsm\Services\Dotenv\SsmDotenv;
use EonX\EasySsm\Tests\AbstractTestCase;
use EonX\EasySsm\Tests\Stubs\EnvLoaderStub;
use EonX\EasySsm\Tests\Stubs\SsmClientStub;

final class SsmDotenvTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testLoadEnv
     */
    public static function providerTestLoadEnv(): iterable
    {
        yield 'no path' => [[new SsmParameter('param', 'string', 'value')], [new EnvData('param', 'value')]];

        yield 'simple path' => [[new SsmParameter('/param', 'string', 'value')], [new EnvData('param', 'value')]];

        yield 'longer path' => [
            [new SsmParameter('/test/env/param', 'string', 'value')],
            [new EnvData('param', 'value')],
            '/test/env/',
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     * @param mixed[] $expected
     *
     * @dataProvider providerTestLoadEnv
     */
    public function testLoadEnv(array $parameters, array $expected, ?string $path = null): void
    {
        $envLoader = new EnvLoaderStub();
        $ssmClient = new SsmClientStub($parameters);
        $ssmPathResolver = new SsmPathResolver();

        $ssmDotenv = new SsmDotenv($ssmClient, $ssmPathResolver, new Parameters(), $envLoader);
        $ssmDotenv->loadEnv($path);

        self::assertEquals($expected, $envLoader->getLoadedEnvs());
    }

    public function testThrowsWithStrictFalse(): void
    {
        $envLoader = new EnvLoaderStub();
        $ssmClient = new SsmClientStub(null, true);
        $ssmPathResolver = new SsmPathResolver();

        $ssmDotenv = new SsmDotenv($ssmClient, $ssmPathResolver, new Parameters(), $envLoader);
        $ssmDotenv->setStrict(false)
            ->loadEnv();

        self::assertEmpty($envLoader->getLoadedEnvs());
    }

    public function testThrowsWithStrictTrue(): void
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('something went wrong');

        $envLoader = new EnvLoaderStub();
        $ssmClient = new SsmClientStub(null, true);
        $ssmPathResolver = new SsmPathResolver();

        $ssmDotenv = new SsmDotenv($ssmClient, $ssmPathResolver, new Parameters(), $envLoader);
        $ssmDotenv->setStrict(true)
            ->loadEnv();
    }
}
