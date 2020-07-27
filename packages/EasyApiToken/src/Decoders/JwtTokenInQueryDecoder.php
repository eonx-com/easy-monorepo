<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface;
use EonX\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class JwtTokenInQueryDecoder implements ApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * @var \EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var string
     */
    private $queryParam;

    public function __construct(JwtFactoryInterface $jwtApiTokenFactory, string $queryParam)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->queryParam = $queryParam;
    }

    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
    {
        $jwtToken = $this->getQueryParam($this->queryParam, $request);

        if (empty($jwtToken)) {
            return null;
        }

        return $this->jwtApiTokenFactory->createFromString((string)$jwtToken);
    }
}
