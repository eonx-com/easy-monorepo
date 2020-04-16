<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces;

interface RequestAttributesAwareInterface
{
    public function getRequestAttributesSetter(): string;
}
