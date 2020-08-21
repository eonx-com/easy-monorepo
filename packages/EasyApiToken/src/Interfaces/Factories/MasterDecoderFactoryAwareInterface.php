<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

/**
 * @deprecated since 2.4. Will be removed in 3.0.
 */
interface MasterDecoderFactoryAwareInterface
{
    public function setMasterFactory(ApiTokenDecoderFactoryInterface $factory): void;
}
