<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit;

use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }

        parent::tearDown();
    }
}