<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface IdFactoryInterface
{
    public function create(): int|string;
}
