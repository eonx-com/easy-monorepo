<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

interface MasterDecoderFactoryAwareInterface
{
    /**
     * Set master factory.
     *
     * @param \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $factory
     *
     * @return void
     */
    public function setMasterFactory(EasyApiTokenDecoderFactoryInterface $factory): void;
}
