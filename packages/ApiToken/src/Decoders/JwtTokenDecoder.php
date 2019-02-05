<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;
use StepTheFkUp\ApiToken\Traits\ApiTokenDecoderTrait;

final class JwtTokenDecoder implements ApiTokenDecoderInterface
{
    use ApiTokenDecoderTrait;

    /**
     * @var string[]
     */
    private $allowedAlgos;

    /**
     * @var string|array
     */
    private $key;

    /**
     * @var null|int
     */
    private $leeway;

    /**
     * JwtTokenDecoder constructor.
     *
     * @param string[] $allowedAlgos
     * @param string|string[] $key
     * @param null|int $leeway
     */
    public function __construct(array $allowedAlgos, $key, ?int $leeway = null)
    {
        $this->allowedAlgos = $allowedAlgos;
        $this->key = $key;
        $this->leeway = $leeway;
    }

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Bearer', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        /**
         * You can add a leeway to account for when there is a clock skew times between
         * the signing and verifying servers. It is recommended that this leeway should
         * not be bigger than a few minutes.
         *
         * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
         */
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        try {
            return new JwtApiToken((array)JWT::decode(\trim($authorization), $this->key, $this->allowedAlgos));
        } catch (Exception $exception) {
            throw new InvalidApiTokenFromRequestException(
                \sprintf(
                    'Decoder "%s" unable to decode token. Message: %s',
                    \get_class($this),
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}