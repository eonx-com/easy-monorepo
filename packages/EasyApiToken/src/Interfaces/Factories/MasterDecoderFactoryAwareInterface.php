<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

interface MasterDecoderFactoryAwareInterface
{
    public function setMasterFactory(EasyApiTokenDecoderFactoryInterface $factory): void;
}
