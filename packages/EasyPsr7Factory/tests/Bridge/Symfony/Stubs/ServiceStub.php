<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs;

use LoyaltyCorp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;

final class ServiceStub
{
    /**
     * @var \LoyaltyCorp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * ServiceStub constructor.
     *
     * @param \LoyaltyCorp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface $psr7Factory
     */
    public function __construct(EasyPsr7FactoryInterface $psr7Factory)
    {
        $this->psr7Factory = $psr7Factory;
    }

    /**
     * Get psr7Factory.
     *
     * @return \LoyaltyCorp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    public function getPsr7Factory(): EasyPsr7FactoryInterface
    {
        return $this->psr7Factory;
    }
}
