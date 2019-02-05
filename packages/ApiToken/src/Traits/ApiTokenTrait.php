<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Traits;

use StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException;

trait ApiTokenTrait
{
    /**
     * Get payload value for given key, if empty it throws an exception.
     *
     * @param string $key
     * @param mixed[] $payload
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    private function getRequiredPayload(string $key, array $payload)
    {
        if (empty($payload[$key]) === false) {
            return $payload[$key];
        }

        throw new EmptyRequiredPayloadException(\sprintf(
            'Required payload "%s" missing on %s',
            $key,
            \get_class($this)
        ));
    }
}