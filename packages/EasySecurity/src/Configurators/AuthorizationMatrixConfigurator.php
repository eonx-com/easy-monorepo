<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class AuthorizationMatrixConfigurator extends AbstractSecurityContextConfigurator
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    public function __construct(AuthorizationMatrixInterface $authorizationMatrix, ?int $priority = null)
    {
        $this->authorizationMatrix = $authorizationMatrix;

        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $context->setAuthorizationMatrix($this->authorizationMatrix);
    }
}
