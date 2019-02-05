<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces;

interface ApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException If encoder doesn't support given apiToken
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException If encoder fails to encode apiToken
     */
    public function encode(ApiTokenInterface $apiToken): string;
}