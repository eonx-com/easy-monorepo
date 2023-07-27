<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

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
