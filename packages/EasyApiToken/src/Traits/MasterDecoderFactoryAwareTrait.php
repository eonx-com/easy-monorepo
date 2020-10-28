<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Traits;

use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;

trait MasterDecoderFactoryAwareTrait
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface
     */
    private $factory;

    public function setMasterFactory(ApiTokenDecoderFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }
}
