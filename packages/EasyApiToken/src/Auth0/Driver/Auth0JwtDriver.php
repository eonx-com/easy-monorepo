<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Auth0\Driver;

use Auth0\SDK\Configuration\SdkConfiguration as Auth0V8SdkConfiguration;
use Auth0\SDK\Token as Auth0V8Token;
use EonX\EasyApiToken\Common\Driver\JwtDriverInterface;
use EonX\EasyApiToken\Common\Exception\InvalidConfigurationException;
use EonX\EasyApiToken\Common\Exception\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Common\Generator\TokenGenerator;
use Psr\Cache\CacheItemPoolInterface;
use Throwable;

final readonly class Auth0JwtDriver implements JwtDriverInterface
{
    private const DEFAULT_ALLOWED_ALGORITHMS = [
        'HS256',
        'RS256',
    ];

    /**
     * @var string[]
     */
    private array $allowedAlgorithms;

    private string $audienceForEncode;

    private string $issuerForEncode;

    /**
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param string[] $allowedAlgorithms
     */
    public function __construct(
        private array $validAudiences,
        array $authorizedIss,
        private string $domain,
        private ?string $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgorithms = null,
        private ?CacheItemPoolInterface $cache = null,
        private ?int $cacheTtl = null,
    ) {
        $this->allowedAlgorithms = $allowedAlgorithms ?? self::DEFAULT_ALLOWED_ALGORITHMS;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->issuerForEncode = (string)\reset($authorizedIss);
    }

    /**
     * @throws \Auth0\SDK\Exception\ConfigurationException
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidEasyApiTokenFromRequestException
     */
    public function decode(string $token): array
    {
        if ($this->domain === '') {
            throw new InvalidConfigurationException('Auth0 requires domain to fetch JWKs');
        }

        // Had to fake clientId to work as it is automatically added to audience
        $config = new Auth0V8SdkConfiguration([
            'audience' => [$this->audienceForEncode],
            'clientId' => 'client_id',
            'domain' => $this->domain,
            'strategy' => Auth0V8SdkConfiguration::STRATEGY_API,
        ]);
        $verifier = new Auth0V8Token($config, $token, Auth0V8Token::TYPE_TOKEN);
        $exceptions = [];

        foreach ($this->allowedAlgorithms as $allowedAlgo) {
            try {
                $verifier = $verifier->verify(
                    $allowedAlgo,
                    null,
                    $this->privateKey,
                    $this->cacheTtl,
                    $this->cache
                );

                return $verifier
                    ->validate($this->issuerForEncode, $this->validAudiences)
                    ->toArray();
            } catch (Throwable $throwable) {
                $exceptions[] = $throwable->getMessage();
            }
        }

        throw new InvalidEasyApiTokenFromRequestException(\sprintf(
            'Could not verify signature. ["%s"]',
            \implode('", "', $exceptions)
        ));
    }

    public function encode(array|object $input): string
    {
        if (\is_object($input)) {
            $input = (array)$input;
        }

        /** @var string $privateKey */
        $privateKey = $this->privateKey;

        $generator = new TokenGenerator($this->audienceForEncode, $privateKey, $this->issuerForEncode);

        return $generator->generate(
            $input['scopes'] ?? [],
            $input['roles'] ?? [],
            $input['sub'] ?? null,
            $input['lifetime'] ?? null,
            false
        );
    }
}
