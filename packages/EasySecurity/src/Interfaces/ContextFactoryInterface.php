<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ContextFactoryInterface
{
    /**
     * Create context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function create(): ContextInterface;
}
