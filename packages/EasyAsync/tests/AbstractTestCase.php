<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    protected function getRandomGenerator(): RandomGeneratorInterface
    {
        return $this->random = $this->random ?? (new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator());
    }

    /**
     * @param mixed $target
     */
    protected function mock($target, ?callable $expectations = null): MockInterface
    {
        $mock = \Mockery::mock($target);

        if ($expectations !== null) {
            \call_user_func($expectations, $mock);
        }

        return $mock;
    }

    protected function tearDown(): void
    {
        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());

        \Mockery::close();

        parent::tearDown();
    }
}
