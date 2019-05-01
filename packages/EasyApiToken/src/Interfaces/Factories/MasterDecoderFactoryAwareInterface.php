<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Factories;

interface MasterDecoderFactoryAwareInterface
{
    /**
     * Set master factory.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $factory
     *
     * @return void
     */
    public function setMasterFactory(EasyApiTokenDecoderFactoryInterface $factory): void;
}
