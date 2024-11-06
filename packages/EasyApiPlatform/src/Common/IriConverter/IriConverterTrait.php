<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\IriConverter;

use ApiPlatform\Api\IriConverterInterface as LegacyIriConverterInterface;
use ApiPlatform\Metadata\IriConverterInterface;

/**
 * @deprecated Remove when API Platform 3 support is dropped
 */

if (\interface_exists(LegacyIriConverterInterface::class) === false) {
    /**
     * @mixin \EonX\EasyApiPlatform\Common\Filter\AdvancedSearchFilter
     */
    trait IriConverterTrait
    {
        protected function getIriConverter(): IriConverterInterface
        {
            // @phpstan-ignore return.type
            return $this->iriConverter;
        }
    }
} else {
    /**
     * @mixin \EonX\EasyApiPlatform\Common\Filter\AdvancedSearchFilter
     */
    trait IriConverterTrait
    {
        protected function getIriConverter(): LegacyIriConverterInterface|IriConverterInterface
        {
            return $this->iriConverter;
        }
    }
}
