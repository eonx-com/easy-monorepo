<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @deprecated since 2.4. Will be removed in 3.0. Use EonX\EasyApiToken\Decoders\BearerTokenDecoder instead.
 */
final class JwtTokenDecoder extends AbstractApiTokenDecoder
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        JwtFactoryInterface $jwtApiTokenFactory,
        ?LoggerInterface $logger = null,
        ?string $name = null
    ) {
        @\trigger_error(
            \sprintf(
                'Using %s is deprecated since 2.4 and will be removed in 3.0. Use %s instead',
                self::class,
                BearerTokenDecoder::class
            ),
            \E_USER_DEPRECATED
        );

        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->logger = $logger ?? new NullLogger();

        parent::__construct($name ?? self::NAME_JWT_HEADER);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function decode($request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Bearer', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        try {
            return $this->jwtApiTokenFactory->createFromString($authorization);
        } catch (InvalidEasyApiTokenFromRequestException $exception) {
            $this->logger->info(\sprintf('Invalid JWT token from request: "%s"', $exception->getMessage()));

            return null;
        }
    }
}
