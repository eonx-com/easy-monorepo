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
     */
    public function encode(ApiTokenInterface $apiToken): string;
}