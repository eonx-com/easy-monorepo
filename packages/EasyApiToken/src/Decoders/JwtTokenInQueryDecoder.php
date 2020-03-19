<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use EonX\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class JwtTokenInQueryDecoder implements EasyApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * @var \EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var string
     */
    private $queryParam;

    public function __construct(JwtEasyApiTokenFactoryInterface $jwtApiTokenFactory, string $queryParam)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->queryParam = $queryParam;
    }

    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        $jwtToken = $this->getQueryParam($this->queryParam, $request);

        if (empty($jwtToken)) {
            return null;
        }

        return $this->jwtApiTokenFactory->createFromString((string)$jwtToken);
    }
}
