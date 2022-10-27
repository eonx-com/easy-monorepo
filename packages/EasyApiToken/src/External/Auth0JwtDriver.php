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

final class Auth0JwtDriver implements JwtDriverInterface
{
    /**
     * @var string[]
     */
    private $allowedAlgos;

    /**
     * @var string
     */
    private $audienceForEncode;

    /**
     * @var string[]
     */
    private $authorizedIss;

    /**
     * @var null|\Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var null|int
     */
    private $cacheTtl;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $issuerForEncode;

    /**
     * @var null|string
     */
    private $privateKey;

    /**
     * @var string[]
     */
    private $validAudiences;

    /**
     * Auth0JwtDriver constructor.
     *
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param null|string[] $allowedAlgos
     */
    public function __construct(
        array $validAudiences,
        array $authorizedIss,
        string $domain,
        ?string $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null,
        ?CacheItemPoolInterface $cache = null,
        ?int $cacheTtl = null
    ) {
        $this->validAudiences = $validAudiences;
        $this->authorizedIss = $authorizedIss;
        $this->domain = $domain;
        $this->privateKey = $privateKey;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->issuerForEncode = (string)\reset($authorizedIss);
        $this->allowedAlgos = $allowedAlgos ?? AlgorithmsInterface::ALL;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @return mixed[]
     *
     * @throws \Auth0\SDK\Exception\ConfigurationException
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
            'domain' => $this->domain,
            'clientId' => 'client_id',
            'strategy' => Auth0V8SdkConfiguration::STRATEGY_API,
            'audience' => [$this->audienceForEncode],
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
            } catch (\Throwable $throwable) {
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
