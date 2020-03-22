<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

interface DecoderNameAwareInterface
{
    public function setDecoderName(string $decoderName): void;
}
