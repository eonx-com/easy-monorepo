<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Encoders;

use Firebase\JWT\JWT;
use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenEncoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;
use StepTheFkUp\ApiToken\Traits\ApiTokenEncoderTrait;

final class JwtTokenEncoder implements ApiTokenEncoderInterface
{
    use ApiTokenEncoderTrait;

    /**
     * @var string
     */
    private $algo;

    /**
     * @var string|resource
     */
    private $privateKey;

    /**
     * JwtTokenEncoder constructor.
     *
     * @param string $algo
     * @param string|resource $privateKey
     */
    public function __construct(string $algo, $privateKey)
    {
        $this->algo = $algo;
        $this->privateKey = $privateKey;
    }

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
        $this->validateToken(JwtApiToken::class, $apiToken);

        try {
            return JWT::encode($apiToken->getPayload(), $this->privateKey, $this->algo);
        } catch (\Exception $exception) {
            throw new UnableToEncodeApiTokenException(
                \sprintf(
                    'In "%s", unable to encode token. Reason: %s',
                    \get_class($this),
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}