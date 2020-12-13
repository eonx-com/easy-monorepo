<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\ApiFormats\Interfaces;

interface SerializableInterface
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
