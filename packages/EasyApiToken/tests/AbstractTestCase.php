<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Diactoros\ServerRequestFactory;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param null|mixed[] $server
     * @param null|mixed[] $query
     */
    protected function createServerRequest(?array $server = null, ?array $query = null): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals($server ?? [], $query ?? []);
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
