<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Factories;

interface DecoderNameAwareInterface
{
    /**
     * Set decoder name.
     *
     * @param string $decoderName
     *
     * @return void
     */
    public function setDecoderName(string $decoderName): void;
}
