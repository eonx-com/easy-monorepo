<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;

final class ContextFactory implements ContextFactoryInterface
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
