<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use EonX\EasyAsync\Batch\BatchFactory;
use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface|null
     */
    private $batchFactory;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface|null
     */
    private $random;

    protected function getBatchFactory(): BatchFactoryInterface
    {
        return $this->batchFactory = $this->batchFactory ?? new BatchFactory($this->getRandomGenerator());
    }

    protected function getRandomGenerator(): RandomGeneratorInterface
    {
        return $this->random = $this->random ?? (new RandomGenerator())->setUuidV4Generator(
            new RamseyUuidV4Generator()
        );
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
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());

        \Mockery::close();

        parent::tearDown();
    }
}
