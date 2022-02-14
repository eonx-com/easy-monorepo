<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Auth0\SDK\Helpers\Cache\CacheHandler;
use Auth0\SDK\Helpers\Tokens\SignatureVerifier;
use Auth0\SDK\JWTVerifier;
use Auth0\SDK\Configuration\SdkConfiguration as Auth0V8SdkConfiguration;
use Auth0\SDK\Token as Auth0V8Token;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\External\Auth0\TokenGenerator;
use EonX\EasyApiToken\External\Auth0V7\TokenVerifier;
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
     * Replace with PSR cache on upgrade to PHP-Auth0 7.
     *
     * @var null|\Auth0\SDK\Helpers\Cache\CacheHandler
     */
    private $cache;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $issuerForEncode;

    /**
     * @var null|string[]|\Auth0\SDK\Helpers\JWKFetcher
     */
    private $jwks;

    /**
     * @var null|string|resource
     */
    private $privateKey;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $v8TokenCache;

    /**
     * @var int
     */
    private $v8TokenCacheTtl;

    /**
     * @var string[]
     */
    private $validAudiences;

    /**
     * Auth0JwtDriver constructor.
     *
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param null|string|resource $privateKey
     * @param null|string[] $allowedAlgos
     * @param \Auth0\SDK\Helpers\Cache\CacheHandler|null $cache Optional Cache handler.
     */
    public function __construct(
        array $validAudiences,
        array $authorizedIss,
        $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null,
        ?CacheHandler $cache = null
    ) {
        $this->validAudiences = $validAudiences;
        $this->authorizedIss = $authorizedIss;
        $this->privateKey = $privateKey;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->allowedAlgos = $allowedAlgos ?? AlgorithmsInterface::ALL;
        $this->cache = $cache;
        $this->issuerForEncode = (string)\reset($authorizedIss);
    }

    /**
     * @return mixed
     *
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function decode(string $token)
    {
        // Auth0 v5
        if (\class_exists(JWTVerifier::class)) {
            return $this->auth0V5Decode($token);
        }

        // Auth0 v7
        if (\class_exists(SignatureVerifier::class)) {
            return $this->auth0V7Decode($token);
        }

        // Auth0 v8
        if (\class_exists(Auth0V8Token::class)) {
            return $this->auth0V8Decode($token);
        }

        throw new InvalidConfigurationException('No supported version of auth0-php installed. Supports only v5 and v7');
    }

    /**
     * @param mixed[] $input
     */
    public function encode($input): string
    {
        /** @var string $privateKey */
        $privateKey = $this->privateKey;

        $generator = new TokenGenerator($this->audienceForEncode, $privateKey, $this->issuerForEncode);

        return $generator->generate(
            $input['scopes'] ?? [],
            $input['roles'] ?? [],
            $input['sub'] ?? null,
            $input['lifetime'] ?? null,
            \class_exists(JWTVerifier::class)
        );
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @param null|string[]|\Auth0\SDK\Helpers\JWKFetcher $jwks
     */
    public function setJwks($jwks): self
    {
        $this->jwks = $jwks;

        return $this;
    }

    public function setV8TokenCache(CacheItemPoolInterface $v8TokenCache): self
    {
        $this->v8TokenCache = $v8TokenCache;

        return $this;
    }

    public function setV8TokenCacheTtl(int $v8TokenCacheTtl): self
    {
        $this->v8TokenCacheTtl = $v8TokenCacheTtl;

        return $this;
    }

    /**
     * @return mixed
     */
    private function auth0V5Decode(string $token)
    {
        $verifier = new JWTVerifier([
            'authorized_iss' => $this->authorizedIss,
            'cache' => $this->cache,
            'client_secret' => $this->privateKey,
            'supported_algs' => $this->allowedAlgos,
            'valid_audiences' => $this->validAudiences,
        ]);

        return $verifier->verifyAndDecode($token);
    }

    /**
     * @return mixed[]
     *
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    private function auth0V7Decode(string $token): array
    {
        if (\is_string($this->privateKey) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'Auth0 v7 accepts privateKey as string only, %s given',
                \gettype($this->privateKey)
            ));
        }

        $verifier = new TokenVerifier(
            $this->authorizedIss,
            $this->validAudiences,
            $this->allowedAlgos,
            $this->privateKey,
            $this->jwks
        );

        return $verifier->verifyAndDecode($token);
    }

    /**
     * @return mixed[]
     *
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    private function auth0V8Decode(string $token): array
    {
        if ($this->privateKey !== null && \is_string($this->privateKey) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'Auth0 v8 accepts privateKey as null or string only, %s given',
                \gettype($this->privateKey)
            ));
        }

        if (\is_string($this->domain) === false || $this->domain === '') {
            throw new InvalidConfigurationException('Auth0 v8 requires domain to fetch JWKs');
        }

        $config = (new Auth0V8SdkConfiguration())->setDomain($this->domain);
        $verifier = new Auth0V8Token($config, $token, Auth0V8Token::TYPE_TOKEN);
        $exceptions = [];

        foreach ($this->allowedAlgos as $allowedAlgo) {
            try {
                $verifier = $verifier->verify(
                    $allowedAlgo,
                    null,
                    $this->privateKey,
                    $this->v8TokenCacheTtl,
                    $this->v8TokenCache
                );

                foreach ($this->authorizedIss as $issuer) {
                    return $verifier
                        ->validate($issuer, $this->validAudiences)
                        ->toArray();
                }
            } catch (\Throwable $throwable) {
                $exceptions[] = $throwable->getMessage();
            }
        }

        throw new InvalidEasyApiTokenFromRequestException(\sprintf(
            'Could not verify signature. ["%s"]',
            \implode('", "', $exceptions)
        ));
    }
}
