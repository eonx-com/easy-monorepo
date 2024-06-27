<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit;

use EonX\EasyPagination\ValueObject\PaginationConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected function createConfig(
        ?string $pageAttr = null,
        ?int $pageDefault = null,
        ?string $perPageAttr = null,
        ?int $perPageDefault = null,
    ): PaginationConfig {
        return new PaginationConfig(
            $pageAttr ?? 'page',
            $pageDefault ?? 1,
            $perPageAttr ?? 'perPage',
            $perPageDefault ?? 15
        );
    }

    protected function createServerRequest(?array $query = null): Request
    {
        $server = [
            'HTTP_HOST' => 'eonx.com',
        ];

        return new Request($query ?? [], [], [], [], [], $server);
    }
}
