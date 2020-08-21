<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since 2.4. Will be removed in 3.0.
 */
final class JwtTokenInQueryDecoder extends AbstractApiTokenDecoder
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var string
     */
    private $queryParam;

    public function __construct(JwtFactoryInterface $jwtApiTokenFactory, string $queryParam, ?string $name = null)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->queryParam = $queryParam;

        parent::__construct($name ?? self::NAME_JWT_PARAM);
    }

    public function decode(Request $request): ?ApiTokenInterface
    {
        $jwtToken = $request->query->get($this->queryParam);

        if (empty($jwtToken)) {
            return null;
        }

        return $this->jwtApiTokenFactory->createFromString((string)$jwtToken);
    }
}
