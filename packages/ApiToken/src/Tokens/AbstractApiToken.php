<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

abstract class AbstractApiToken implements ApiTokenInterface
{
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * AbstractApiToken constructor.
     *
     * @param mixed[] $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get payload value for given key, if empty it throws an exception.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    protected function getRequiredPayload(string $key)
    {
        if (empty($this->payload[$key]) === false) {
            return $this->payload[$key];
        }

        throw new EmptyRequiredPayloadException(\sprintf(
            'Required payload "%s" missing on API token "%s"',
            $key,
            \get_class($this)
        ));
    }
}