<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;

final class ContextFactoryInterfaceStub implements ContextFactoryInterface
{
    /**
     * Create context.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function create(ContextResolvingDataInterface $data): ContextInterface
    {
        return new Context($data->getApiToken(), $data->getRoles(), $data->getProvider(), $data->getUser());
    }
}
