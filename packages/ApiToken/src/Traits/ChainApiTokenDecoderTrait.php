<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Traits;

use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;

trait ChainApiTokenDecoderTrait
{
    /**
     * Validate given decoder implements the right interface, otherwise throw exception.
     *
     * @param mixed $decoder
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    private function validateDecoder($decoder): void
    {
        if ($decoder instanceof ApiTokenDecoderInterface) {
            return;
        }

        throw new InvalidArgumentException(\sprintf(
            'In "%s", decoder must be an instance of "%s", "%s" given',
            \get_class($this),
            ApiTokenDecoderInterface::class,
            gettype($decoder)
        ));
    }

    /**
     * Validate given array of decoders isn't empty, and all of them implement the right interface.
     *
     * @param mixed[] $decoders
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    private function validateDecoders(array $decoders): void
    {
        if (empty($decoders)) {
            throw new InvalidArgumentException(\sprintf('In "%s", empty array of decoders given', \get_class($this)));
        }

        foreach ($decoders as $decoder) {
            $this->validateDecoder($decoder);
        }
    }
}
