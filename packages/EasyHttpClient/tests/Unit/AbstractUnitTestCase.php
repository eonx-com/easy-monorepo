<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
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
     * @param class-string $target
     */
    protected function mock(string $target, ?callable $expectations = null): MockInterface
    {
        /** @var \Mockery\MockInterface $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
