<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException;
use StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;
use StepTheFkUp\ApiToken\Traits\ApiTokenDecoderTrait;

final class JwtTokenDecoder implements ApiTokenDecoderInterface
{
    use ApiTokenDecoderTrait;

    /**
     * @var \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtTokenDecoder constructor.
     *
     * @param \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     */
    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
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

        try {
            return new JwtApiToken((array)$this->jwtDriver->decode(\trim($authorization)));
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