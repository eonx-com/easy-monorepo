<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Decoder;

use EonX\EasyApiToken\Common\Driver\JwtDriverInterface;
use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class BearerTokenDecoder extends AbstractDecoder
{
    public function __construct(
        private readonly JwtDriverInterface $jwtDriver,
        ?string $name = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct($name);
    }

    public function decode(Request $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Bearer', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Bearer, return null
            return null;
        }

        try {
            return new Jwt((array)$this->jwtDriver->decode(\trim($authorization)), $authorization);
        } catch (Throwable $throwable) {
            $this->logger?->info(\sprintf('Invalid JWT token from request: "%s"', $throwable->getMessage()));

            // Return null not to break chain decoder
            return null;
        }
    }
}
