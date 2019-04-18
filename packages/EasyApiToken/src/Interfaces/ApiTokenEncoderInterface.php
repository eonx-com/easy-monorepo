<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces;

interface EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException If encoder doesn't support given apiToken
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoder fails to encode apiToken
     */
    public function encode(EasyApiTokenInterface $apiToken): string;
}

\class_alias(
    EasyApiTokenEncoderInterface::class,
    'LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface',
    false
);
