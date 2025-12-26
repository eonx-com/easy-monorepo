<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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

    protected function tearDown(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());

        Mockery::close();

        parent::tearDown();
    }

    /**
     * @template TMock of object
     *
     * @param class-string<TMock> $target
     *
     * @return \Mockery\LegacyMockInterface&\Mockery\MockInterface&TMock
     */
    protected function mock(mixed $target, ?callable $expectations = null): object
    {
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&TMock $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
