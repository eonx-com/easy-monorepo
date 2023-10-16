<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\Listeners;

use ApiPlatform\State\Util\OperationRequestInitiatorTrait as NewOperationRequestInitiatorTrait;
use ApiPlatform\Util\OperationRequestInitiatorTrait as OldOperationRequestInitiatorTrait;

if (trait_exists('ApiPlatform\Util\OperationRequestInitiatorTrait')) {
    /**
     * @deprecated Remove when ApiPlatform 3.2 is required
     */
    trait OperationRequestInitiatorTrait
    {
        use OldOperationRequestInitiatorTrait;
    }
}

if (trait_exists('ApiPlatform\State\Util\OperationRequestInitiatorTrait')) {
    /**
     * @deprecated Remove when ApiPlatform 3.2 is required
     */
    trait OperationRequestInitiatorTrait
    {
        use NewOperationRequestInitiatorTrait;
    }
}


