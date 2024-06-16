<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit;

use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;

    /**
     * Get mock for given class and set expectations based on given callable.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     */
    protected function getMockWithExpectations(string $class, callable $setExpectations): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        $setExpectations($mock);

        return $mock;
    }
}
