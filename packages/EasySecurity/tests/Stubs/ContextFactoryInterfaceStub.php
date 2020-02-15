<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;

final class ContextFactoryInterfaceStub implements ContextFactoryInterface
{
    /**
     * Create context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function create(): ContextInterface
    {
        return new Context();
    }
}
