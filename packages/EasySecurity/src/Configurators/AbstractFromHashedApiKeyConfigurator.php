<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKeyInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromHashedApiKeyConfigurator extends AbstractSecurityContextConfigurator
{
    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof HashedApiKeyInterface === false) {
            return;
        }

        $this->doConfigure($context, $request, $token);
    }

    abstract protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        HashedApiKeyInterface $apiKey,
    ): void;
}
