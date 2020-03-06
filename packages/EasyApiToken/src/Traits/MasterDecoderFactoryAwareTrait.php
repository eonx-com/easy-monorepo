<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Traits;

use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;

trait MasterDecoderFactoryAwareTrait
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $factory;

    public function setMasterFactory(EasyApiTokenDecoderFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }
}
