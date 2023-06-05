<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\JwtClaimFetcher;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromJwtConfigurator extends AbstractSecurityContextConfigurator
{
    /**
     * @var string
     */
    private $jwtClaim;

    /**
     * @var \EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface
     */
    private $jwtClaimFetcher;

    public function __construct(string $jwtClaim, ?int $priority = null)
    {
        $this->jwtClaim = $jwtClaim;

        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof JwtInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */

        $this->doConfigure($context, $request, $token);
    }

    public function setJwtClaimFetcher(JwtClaimFetcherInterface $jwtClaimFetcher): void
    {
        $this->jwtClaimFetcher = $jwtClaimFetcher;
    }

    abstract protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        JwtInterface $token,
    ): void;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    protected function getClaim(JwtInterface $token, string $claim, $default = null)
    {
        return $this->getJwtClaimFetcher()
            ->getClaim($token, $claim, $default);
    }

    /**
     * @param null|mixed[] $default
     *
     * @return mixed[]
     */
    protected function getMainClaim(JwtInterface $token, ?array $default = null): array
    {
        return $this->getJwtClaimFetcher()
            ->getArrayClaim($token, $this->jwtClaim, $default);
    }

    private function getJwtClaimFetcher(): JwtClaimFetcherInterface
    {
        if ($this->jwtClaimFetcher !== null) {
            return $this->jwtClaimFetcher;
        }

        return $this->jwtClaimFetcher = new JwtClaimFetcher();
    }
}
