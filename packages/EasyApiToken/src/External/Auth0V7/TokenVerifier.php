<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\Auth0V7;

use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\SignatureVerifier;
use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Interfaces\AlgorithmsInterface;
use Lcobucci\JWT\Token;

final class TokenVerifier
{
    /**
     * @var string[]
     */
    private $allowedAlgos;

    /**
     * @var string[]
     */
    private $authorizedIss;

    /**
     * @var null|string[]|\Auth0\SDK\Helpers\JWKFetcher
     */
    private $jwks;

    /**
     * @var null|string
     */
    private $privateKey;

    /**
     * @var string[]
     */
    private $validAudiences;

    /**
     * @param string[] $authorizedIss
     * @param string[] $validAudiences
     * @param null|string[] $allowedAlgos
     * @param null|string[]|\Auth0\SDK\Helpers\JWKFetcher $jwks
     */
    public function __construct(
        array $authorizedIss,
        array $validAudiences,
        ?array $allowedAlgos = null,
        ?string $privateKey = null,
        $jwks = null
    ) {
        $this->authorizedIss = $authorizedIss;
        $this->validAudiences = $validAudiences;
        $this->allowedAlgos = $allowedAlgos ?? AlgorithmsInterface::ALL;
        $this->privateKey = $privateKey;
        $this->jwks = $jwks;
    }

    /**
     * @return mixed[]
     *
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function verifyAndDecode(string $token): array
    {
        if ($token === '') {
            throw new InvalidTokenException('ID token is required but missing');
        }

        $verifiedToken = $this->verifySignatureAndDecode($token);

        $tokenIss = $verifiedToken->getClaim('iss', false);

        if (\is_string($tokenIss) === false) {
            throw new InvalidTokenException('Issuer (iss) claim must be a string present in the ID token');
        }

        if (\in_array($tokenIss, $this->authorizedIss, true) === false) {
            throw new InvalidTokenException(\sprintf(
                'Issuer (iss) claim mismatch in the ID token; found "%s", expected one of ["%s"]',
                $tokenIss,
                \implode('", "', $this->authorizedIss)
            ));
        }

        $tokenAud = $verifiedToken->getClaim('aud', false);
        if (\is_string($tokenAud) === false && \is_array($tokenAud) === false) {
            throw new InvalidTokenException(
                'Audience (aud) claim must be a string or array of strings present in the ID token'
            );
        }

        $tokenAud = (array)$tokenAud;
        $resolvedAudience = null;

        foreach ($tokenAud as $audience) {
            if (\in_array($audience, $this->validAudiences, true)) {
                $resolvedAudience = $audience;

                break;
            }
        }
        if ($resolvedAudience === null) {
            throw new InvalidTokenException(sprintf(
                'Audience (aud) claim mismatch in the ID token; expected one of ["%s"] found ["%s"]',
                \implode('", "', $this->validAudiences),
                \implode('", "', $tokenAud)
            ));
        }

        $now = \time();
        $leeway = 60;

        $tokenExp = $verifiedToken->getClaim('exp', false);
        if (\is_int($tokenExp) === false) {
            throw new InvalidTokenException('Expiration Time (exp) claim must be a number present in the ID token');
        }

        $expireTime = $tokenExp + $leeway;
        if ($now > $expireTime) {
            throw new InvalidTokenException(sprintf(
                'Expiration Time (exp) claim error in the ID token; current time (%d) is after expiration time (%d)',
                $now,
                $expireTime
            ));
        }

        $profile = [];
        foreach ($verifiedToken->getClaims() as $claim => $value) {
            $profile[$claim] = $value->getValue();
        }

        return $profile;
    }

    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function resolveSignatureVerifier(string $algo): SignatureVerifier
    {
        if ($algo === AlgorithmsInterface::HS256) {
            if (\is_string($this->privateKey) === false) {
                throw new InvalidConfigurationException(\sprintf(
                    'PrivateKey must be a string when using algorithm "%s"',
                    $algo
                ));
            }

            return new SymmetricVerifier($this->privateKey);
        }

        if ($algo === AlgorithmsInterface::RS256) {
            if ($this->jwks === null) {
                throw new InvalidConfigurationException(\sprintf(
                    'Jwks must be set when using algorithm "%s"',
                    $algo
                ));
            }

            return new AsymmetricVerifier($this->jwks);
        }

        throw new InvalidArgumentException(\sprintf(
            'Given algorithm "%s" invalid, expected one of ["%s"]',
            $algo,
            \implode('", "', AlgorithmsInterface::ALL)
        ));
    }

    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    private function verifySignatureAndDecode(string $token): Token
    {
        if (\count($this->allowedAlgos) < 1) {
            throw new InvalidConfigurationException(\sprintf('No allowed algorithms configured on %s', self::class));
        }

        $exceptions = [];

        foreach ($this->allowedAlgos as $algo) {
            $verifier = $this->resolveSignatureVerifier($algo);

            try {
                return $verifier->verifyAndDecode($token);
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
