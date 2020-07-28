<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtFactoryInterface;
use EonX\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class JwtTokenDecoder extends AbstractApiTokenDecoder
{
    use EasyApiTokenDecoderTrait;

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
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->logger = $logger ?? new NullLogger();

        parent::__construct($name ?? self::NAME_JWT_HEADER);
    }

    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Bearer', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        try {
            return $this->jwtApiTokenFactory->createFromString($authorization);
        } catch (InvalidEasyApiTokenFromRequestException $exception) {
            $this->logger->info(\sprintf('Invalid JWT token from request: "%s"', $exception->getMessage()));

            return null;
        }
    }
}
