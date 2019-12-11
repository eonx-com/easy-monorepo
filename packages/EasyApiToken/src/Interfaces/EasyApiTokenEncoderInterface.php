<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException If given apiToken not supported
     * @throws \EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoding fails
     */
    public function encode(EasyApiTokenInterface $apiToken): string;
}
