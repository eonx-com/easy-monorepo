<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface;
use StepTheFkUp\ApiToken\Traits\ApiTokenDecoderTrait;

final class JwtTokenInQueryDecoder implements ApiTokenDecoderInterface
{
    use ApiTokenDecoderTrait;

    /**
     * @var \StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var string
     */
    private $queryParam;

    /**
     * JwtTokenInQueryDecoder constructor.
     *
     * @param \StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface $jwtApiTokenFactory
     * @param string $queryParam
     */
    public function __construct(JwtApiTokenFactoryInterface $jwtApiTokenFactory, string $queryParam)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->queryParam = $queryParam;
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
        $jwtToken = $this->getQueryParam($this->queryParam, $request);

        if (empty($jwtToken)) {
            return null;
        }

        return $this->jwtApiTokenFactory->createFromString((string)$jwtToken);
    }
}
