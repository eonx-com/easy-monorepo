<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Traits;

use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;

trait MasterDecoderFactoryAwareTrait
{
    /**
     * @var \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $factory;

    /**
     * Set master factory.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $factory
     *
     * @return void
     */
    public function setMasterFactory(EasyApiTokenDecoderFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }
}
