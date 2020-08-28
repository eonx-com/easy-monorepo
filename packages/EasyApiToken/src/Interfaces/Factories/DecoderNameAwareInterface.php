<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

/**
 * @deprecated since 2.4. Will be removed in 3.0.
 */
interface DecoderNameAwareInterface
{
    public function setDecoderName(string $decoderName): void;
}
