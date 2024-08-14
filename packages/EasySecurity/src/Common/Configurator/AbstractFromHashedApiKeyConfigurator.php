<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromHashedApiKeyConfigurator extends AbstractSecurityContextConfigurator
{
    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        if ($token instanceof HashedApiKey === false) {
            return;
        }

        $this->doConfigure($context, $request, $token);
    }

    abstract protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        HashedApiKey $apiKey,
    ): void;
}
