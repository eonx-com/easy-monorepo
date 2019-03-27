<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface;
use StepTheFkUp\ApiToken\Traits\ApiTokenDecoderTrait;

final class JwtTokenDecoder implements ApiTokenDecoderInterface
{
    use ApiTokenDecoderTrait;

    /**
     * @var \StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * JwtTokenDecoder constructor.
     *
     * @param \StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface $jwtApiTokenFactory
     */
    public function __construct(JwtApiTokenFactoryInterface $jwtApiTokenFactory)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
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

        return $this->jwtApiTokenFactory->createFromString($authorization);
    }
}
