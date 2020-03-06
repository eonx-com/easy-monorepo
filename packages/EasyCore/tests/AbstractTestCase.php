<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
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
}
