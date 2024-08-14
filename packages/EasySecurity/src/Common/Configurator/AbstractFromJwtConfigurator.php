<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolver;
use EonX\EasySecurity\Common\Resolver\JwtClaimResolverInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromJwtConfigurator extends AbstractSecurityContextConfigurator
{
    private ?JwtClaimResolverInterface $jwtClaimFetcher = null;

    public function __construct(
        private readonly string $jwtClaim,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof Jwt === false) {
            return;
        }

        $this->doConfigure($context, $request, $token);
    }

    public function setJwtClaimFetcher(JwtClaimResolverInterface $jwtClaimFetcher): void
    {
        $this->jwtClaimFetcher = $jwtClaimFetcher;
    }

    abstract protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        Jwt $token,
    ): void;

    protected function getClaim(Jwt $token, string $claim, mixed $default = null): mixed
    {
        return $this->getJwtClaimFetcher()
            ->getClaim($token, $claim, $default);
    }

    protected function getMainClaim(Jwt $token, ?array $default = null): array
    {
        return $this->getJwtClaimFetcher()
            ->getArrayClaim($token, $this->jwtClaim, $default);
    }

    private function getJwtClaimFetcher(): JwtClaimResolverInterface
    {
        if ($this->jwtClaimFetcher !== null) {
            return $this->jwtClaimFetcher;
        }

        return $this->jwtClaimFetcher = new JwtClaimResolver();
    }
}
