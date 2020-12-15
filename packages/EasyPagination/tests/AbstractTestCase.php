<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function createConfig(
        ?string $numberAttr = null,
        ?int $numberDefault = null,
        ?string $sizeAttr = null,
        ?int $sizeDefault = null
    ): StartSizeConfig {
        return new StartSizeConfig(
            $numberAttr ?? 'number',
            $numberDefault ?? 1,
            $sizeAttr ?? 'size',
            $sizeDefault ?? 15
        );
    }

    /**
     * @param null|mixed[] $query
     */
    protected function createServerRequest(?array $query = null): Request
    {
        $server = [
            'HTTP_HOST' => 'eonx.com',
        ];

        return new Request($query ?? [], [], [], [], [], $server);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }
}
