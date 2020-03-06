<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests;

use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param string|object $class
     */
    protected function mock($class, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
