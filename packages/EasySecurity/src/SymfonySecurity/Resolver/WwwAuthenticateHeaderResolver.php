<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Resolver;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasyApiToken\Common\ValueObject\BasicAuth;
use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final readonly class WwwAuthenticateHeaderResolver
{
    private const CHALLENGE_API_KEY = 'Basic realm="api-key"';

    private const CHALLENGE_BASIC_AUTH = 'Basic realm="user"';

    private const CHALLENGE_BEARER = 'Bearer';

    public function __construct(
        private string $defaultChallenge,
        private ?ApiTokenDecoderFactoryInterface $apiTokenDecoderFactory = null,
        private ?string $apiTokenDecoder = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function resolve(Request $request): string
    {
        try {
            $token = $this->apiTokenDecoderFactory?->build($this->apiTokenDecoder)
                ->decode($request);
        } catch (Throwable $throwable) {
            $this->logger->warning('Cannot resolve WWW-Authenticate challenge, the default one is used', [
                'exception' => $throwable,
            ]);

            return $this->defaultChallenge;
        }

        return match (true) {
            $token instanceof Jwt => self::CHALLENGE_BEARER,
            $token instanceof ApiKey, $token instanceof HashedApiKey => self::CHALLENGE_API_KEY,
            $token instanceof BasicAuth => self::CHALLENGE_BASIC_AUTH,
            default => $this->defaultChallenge,
        };
    }
}
