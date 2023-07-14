<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    /**
     * @param null|mixed[] $server
     * @param null|mixed[] $query
     */
    protected function createRequest(?array $server = null, ?array $query = null): Request
    {
        return new Request($query ?? [], [], [], [], [], $server ?? []);
    }
}
