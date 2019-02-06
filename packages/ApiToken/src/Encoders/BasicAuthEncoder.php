<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Encoders;

use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenEncoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;
use StepTheFkUp\ApiToken\Traits\ApiTokenEncoderTrait;

final class BasicAuthEncoder implements ApiTokenEncoderInterface
{
    use ApiTokenEncoderTrait;

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
    public function encode(ApiTokenInterface $apiToken): string
    {
        $this->validateToken(BasicAuthApiTokenInterface::class, $apiToken);

        /** @var \StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface $apiToken */
        $username = $apiToken->getUsername();
        $password = $apiToken->getPassword();

        if (empty($username) === false && empty($password) === false) {
            return \base64_encode(\sprintf('%s:%s', $username, $password));
        }

        throw new UnableToEncodeApiTokenException(\sprintf(
            'In "%s", username and/or password empty. Payload: [username => "%s", password => "%s"]',
            \get_class($this),
            $username,
            $password
        ));
    }
}