<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use EonX\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class JwtTokenDecoder implements EasyApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * @var \EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface
     */
    private $jwtApiTokenFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(JwtEasyApiTokenFactoryInterface $jwtApiTokenFactory, ?LoggerInterface $logger = null)
    {
        $this->jwtApiTokenFactory = $jwtApiTokenFactory;
        $this->logger = $logger ?? new NullLogger();
    }

    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
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
