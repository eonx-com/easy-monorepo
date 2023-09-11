<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Routing;

interface SelfProvidedIriItemInterface
{
    public function getIri(): string;
}
