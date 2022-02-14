<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Auth0\SDK\Helpers\Tokens\SignatureVerifier;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\External\Auth0\TokenGenerator;
use EonX\EasyApiToken\External\Auth0V7\TokenVerifier;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\AlgorithmsInterface;

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
     * @var string
     */
    private $issuerForEncode;

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
     * Auth0JwtDriver constructor.
     *
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param null|string[] $allowedAlgos
     */
    public function __construct(
        array $validAudiences,
        array $authorizedIss,
        ?string $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null
    ) {
        $this->validAudiences = $validAudiences;
        $this->authorizedIss = $authorizedIss;
        $this->privateKey = $privateKey;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->allowedAlgos = $allowedAlgos ?? AlgorithmsInterface::ALL;
        $this->issuerForEncode = (string)\reset($authorizedIss);
    }

    /**
     * @return mixed[]
     *
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function decode(string $token): array
    {
        // Auth0 v7
        if (\class_exists(SignatureVerifier::class)) {
            return $this->auth0V7Decode($token);
        }

        throw new InvalidConfigurationException('No supported version of auth0-php installed. Supports only v7.');
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

    /**
     * @param null|string[]|\Auth0\SDK\Helpers\JWKFetcher $jwks
     */
    public function setJwks($jwks): self
    {
        $this->jwks = $jwks;

        return $this;
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
        $verifier = new TokenVerifier(
            $this->authorizedIss,
            $this->validAudiences,
            $this->allowedAlgos,
            $this->privateKey,
            $this->jwks
        );

        return $verifier->verifyAndDecode($token);
    }
}
