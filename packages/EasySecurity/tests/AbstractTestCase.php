<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Resolvers\ContextResolvingData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Create context resolving data.
     *
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $token
     * @param null|\Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    protected function createContextResolvingData(
        ?EasyApiTokenInterface $token = null,
        ?Request $request = null
    ): ContextResolvingDataInterface {
        return new ContextResolvingData($request ?? new Request(), $token);
    }
}
