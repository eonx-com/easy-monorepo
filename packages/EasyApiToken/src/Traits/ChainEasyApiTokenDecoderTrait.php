<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Traits;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;

trait ChainEasyApiTokenDecoderTrait
{
    /**
     * @param mixed $decoder
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    private function validateDecoder($decoder): void
    {
        if ($decoder instanceof ApiTokenDecoderInterface) {
            return;
        }

        throw new InvalidArgumentException(\sprintf(
            'In "%s", decoder must be an instance of "%s", "%s" given',
            static::class,
            ApiTokenDecoderInterface::class,
            \gettype($decoder)
        ));
    }

    /**
     * @param mixed[] $decoders
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    private function validateDecoders(array $decoders): void
    {
        if (empty($decoders)) {
            throw new InvalidArgumentException(\sprintf('In "%s", empty array of decoders given', static::class));
        }

        foreach ($decoders as $decoder) {
            $this->validateDecoder($decoder);
        }
    }
}
