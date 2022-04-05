<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Symfony\Providers;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractFirewallAwareDecoderProvider implements ApiTokenDecoderProviderInterface
{
    private const NO_FIREWALL = 'easy_api_token_no_firewall';

    private ?string $firewall = null;

    private ?FirewallMap $firewallMap = null;

    private ?RequestStack $requestStack = null;

    /**
     * @return iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
     */
    public function getDecoders(): iterable
    {
        return $this->doGetDecoders($this->resolveFirewall());
    }

    public function getDefaultDecoder(): ?string
    {
        return $this->doGetDefaultDecoder($this->resolveFirewall());
    }

    #[Required]
    public function setFirewallMap(FirewallMap $firewallMap): void
    {
        $this->firewallMap = $firewallMap;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
     */
    abstract protected function doGetDecoders(?string $firewall = null): iterable;

    abstract protected function doGetDefaultDecoder(?string $firewall = null): ?string;

    private function resolveFirewall(): ?string
    {
        $this->initFirewall();

        return $this->firewall !== self::NO_FIREWALL ? $this->firewall : null;
    }

    private function initFirewall(): void
    {
        if ($this->firewall !== null) {
            return;
        }

        $request = $this->requestStack?->getMainRequest();
        $firewallConfig = $request ? $this->firewallMap?->getFirewallConfig($request) : null;

        $this->firewall = $firewallConfig?->getName() ?? self::NO_FIREWALL;
    }
}
