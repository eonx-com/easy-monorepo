<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }
    }

    protected static function getRequestWithHeaders(?array $headers = null): Request
    {
        $formatted = [];

        foreach ($headers ?? [] as $name => $value) {
            $formatted[\sprintf('HTTP_%s', $name)] = $value;
        }

        return new Request([], [], [], [], [], $formatted);
    }
}