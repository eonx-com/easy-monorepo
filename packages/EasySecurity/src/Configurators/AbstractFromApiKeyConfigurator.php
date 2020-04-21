<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromApiKeyConfigurator extends AbstractSecurityContextConfigurator
{
    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof ApiKeyEasyApiTokenInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $token */

        $this->doConfigure($context, $request, $token);
    }

    abstract protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        ApiKeyEasyApiTokenInterface $apiKey
    ): void;
}
