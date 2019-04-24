<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Traits;

use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;

trait ChainEasyApiTokenDecoderTrait
{
    /**
     * Validate given decoder implements the right interface, otherwise throw exception.
     *
     * @param mixed $decoder
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    private function validateDecoder($decoder): void
    {
        if ($decoder instanceof EasyApiTokenDecoderInterface) {
            return;
        }

        throw new InvalidArgumentException(\sprintf(
            'In "%s", decoder must be an instance of "%s", "%s" given',
            \get_class($this),
            EasyApiTokenDecoderInterface::class,
            \gettype($decoder)
        ));
    }

    /**
     * Validate given array of decoders isn't empty, and all of them implement the right interface.
     *
     * @param mixed[] $decoders
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
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

\class_alias(
    ChainEasyApiTokenDecoderTrait::class,
    'LoyaltyCorp\EasyApiToken\Traits\ChainEasyApiTokenDecoderTrait',
    false
);
