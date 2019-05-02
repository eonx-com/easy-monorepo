<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Traits;

trait DecoderNameAwareTrait
{
    /**
     * @var string
     */
    protected $decoderName;

    /**
     * Set decoder name.
     *
     * @param string $decoderName
     *
     * @return void
     */
    public function setDecoderName(string $decoderName): void
    {
        $this->decoderName = $decoderName;
    }
}
