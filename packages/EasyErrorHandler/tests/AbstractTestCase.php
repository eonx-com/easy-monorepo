<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $files = [__DIR__ . '/../var', __DIR__ . '/Bridge/Symfony/tmp_config.yaml'];

        foreach ($files as $file) {
            if ($fs->exists($file)) {
                $fs->remove($file);
            }
        }

        parent::tearDown();
    }
}
