<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit;

use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractUnitTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected function mock(mixed $target, ?callable $expectations = null): MockInterface
    {
        /** @var \Mockery\MockInterface $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
