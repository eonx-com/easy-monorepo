<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTestCase extends TestCase
{
    private ?RandomGeneratorInterface $random = null;

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

    protected function getRandomGenerator(): RandomGeneratorInterface
    {
        if ($this->random !== null) {
            return $this->random;
        }

        $this->random = (new RandomGenerator())->setUuidV4Generator(
            new RamseyUuidV4Generator()
        );

        return $this->random;
    }

    protected function mock(mixed $target, ?callable $expectations = null): MockInterface
    {
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
