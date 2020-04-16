<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces;

interface SelfProvidedIriItemInterface
{
    public function getIri(): string;
}
