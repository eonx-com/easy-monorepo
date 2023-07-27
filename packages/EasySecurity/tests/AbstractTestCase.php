<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    use ProphecyTrait;

    protected static string $mainJwtClaim = 'https://eonx.com/user';

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
