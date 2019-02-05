<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Traits;

use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

trait ApiTokenEncoderTrait
{
    /**
     * Validate given API token is an instance of expected class.
     *
     * @param string $class
     * @param \StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface $apiToken
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    private function validateToken(string $class, ApiTokenInterface $apiToken): void
    {
        if ($class === \get_class($apiToken)) {
            return;
        }

        throw new InvalidArgumentException(\sprintf(
            'In "%s", API token expected to be instance of "%s", "%s" given.',
            \get_class($this),
            $class,
            \get_class($apiToken)
        ));
    }
}