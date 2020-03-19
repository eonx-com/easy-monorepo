<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;

final class ServiceStub
{
    /**
     * @var \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    public function __construct(EasyPsr7FactoryInterface $psr7Factory)
    {
        $this->psr7Factory = $psr7Factory;
    }

    public function getPsr7Factory(): EasyPsr7FactoryInterface
    {
        return $this->psr7Factory;
    }
}
