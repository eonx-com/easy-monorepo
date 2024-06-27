<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $files = [__DIR__ . '/../../var'];

        foreach ($files as $file) {
            if ($fs->exists($file)) {
                $fs->remove($file);
            }
        }

        parent::tearDown();
    }
}
