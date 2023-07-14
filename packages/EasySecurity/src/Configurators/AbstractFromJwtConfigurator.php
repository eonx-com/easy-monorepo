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
    private ?JwtClaimFetcherInterface $jwtClaimFetcher = null;

    public function __construct(
        private string $jwtClaim,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof JwtInterface === false) {
            return;
        }

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

    protected function getClaim(JwtInterface $token, string $claim, mixed $default = null): mixed
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
