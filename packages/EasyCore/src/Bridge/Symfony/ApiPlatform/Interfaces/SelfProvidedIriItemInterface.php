<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces;

/**
 * @deprecated since 4.2.8, will be removed in 5.0. Use EonX\EasyApiPlatform\Routing\SelfProvidedIriItemInterface instead.
 */
interface SelfProvidedIriItemInterface
{
    public function getIri(): string;
}
