<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Decoders;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use LoyaltyCorp\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class JwtTokenInQueryDecoder implements EasyApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var string
     */
    private $queryParam;

    /**
     * JwtTokenInQueryDecoder constructor.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface $jwtApiTokenFactory
     * @param string $queryParam
     */
    public function __construct(JwtEasyApiTokenFactoryInterface $jwtApiTokenFactory, string $queryParam)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->queryParam = $queryParam;
    }

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        $jwtToken = $this->getQueryParam($this->queryParam, $request);

        if (empty($jwtToken)) {
            return null;
        }

        return $this->jwtApiTokenFactory->createFromString((string)$jwtToken);
    }
}

\class_alias(
    JwtTokenInQueryDecoder::class,
    'StepTheFkUp\EasyApiToken\Decoders\JwtTokenInQueryDecoder',
    false
);
