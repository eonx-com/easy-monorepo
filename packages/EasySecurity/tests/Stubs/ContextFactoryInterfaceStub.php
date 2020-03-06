<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;

final class ContextFactoryInterfaceStub implements ContextFactoryInterface
{
    public function create(): ContextInterface
    {
        return new Context();
    }
}
