<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Tokens\Jwt;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class BearerTokenDecoder extends AbstractApiTokenDecoder
{
    public function __construct(
        private JwtDriverInterface $jwtDriver,
        ?string $name = null,
        private LoggerInterface $logger = new NullLogger(),
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
            $this->logger->info(\sprintf('Invalid JWT token from request: "%s"', $throwable->getMessage()));

            // Return null not to break chain decoder
            return null;
        }
    }
}
