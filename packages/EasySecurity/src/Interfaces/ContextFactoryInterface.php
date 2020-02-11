<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ContextFactoryInterface
{
    /**
     * Create context.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function create(): ContextInterface;
}
