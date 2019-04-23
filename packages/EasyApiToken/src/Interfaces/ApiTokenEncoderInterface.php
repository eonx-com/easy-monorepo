<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces;

interface EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException If encoder doesn't support given apiToken
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoder fails to encode apiToken
     */
    public function encode(EasyApiTokenInterface $apiToken): string;
}

\class_alias(
    EasyApiTokenEncoderInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface',
    false
);
