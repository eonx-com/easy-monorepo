<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit;

use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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

    /**
     * @template TMock of object
     *
     * @param class-string<TMock> $target
     *
     * @return LegacyMockInterface&MockInterface&TMock
     */
    protected function mock(mixed $target, ?callable $expectations = null): object
    {
        /** @var LegacyMockInterface&MockInterface&TMock $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
