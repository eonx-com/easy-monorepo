<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Auth0\SDK\Configuration\SdkConfiguration as Auth0V8SdkConfiguration;
use Auth0\SDK\Token as Auth0V8Token;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\External\Auth0\TokenGenerator;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\AlgorithmsInterface;
use Psr\Cache\CacheItemPoolInterface;
use Throwable;

final class Auth0JwtDriver implements JwtDriverInterface
{
    /**
     * @var string[]
     */
    private array $allowedAlgos;

    private string $audienceForEncode;

    private string $issuerForEncode;

    /**
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param string[]|null $allowedAlgos
     */
    public function __construct(
        private array $validAudiences,
        private array $authorizedIss,
        private string $domain,
        private ?string $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null,
        private ?CacheItemPoolInterface $cache = null,
        private ?int $cacheTtl = null,
    ) {
        $this->allowedAlgos = $allowedAlgos ?? AlgorithmsInterface::ALL;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->issuerForEncode = (string)\reset($authorizedIss);
    }

    /**
     @throws \Auth0\SDK\Exception\ConfigurationException
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
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

        foreach ($this->allowedAlgos as $allowedAlgo) {
            try {
                $verifier = $verifier->verify(
                    $allowedAlgo,
                    null,
                    $this->privateKey,
                    $this->cacheTtl,
                    $this->cache
                );
                $tokenIssuer = (string)\reset($this->authorizedIss);

                return $verifier
                    ->validate($tokenIssuer, $this->validAudiences)
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
