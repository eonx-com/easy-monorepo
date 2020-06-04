<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DataCollector;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class SecurityContextDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'easy_security.security_context_collector';

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null): void
    {
        $this->data['authorization_matrix'] = $this->securityContext->getAuthorizationMatrix();
        $this->data['permissions'] = $this->securityContext->getPermissions();
        $this->data['roles'] = $this->securityContext->getRoles();
        $this->data['provider'] = $this->securityContext->getProvider();
        $this->data['user'] = $this->securityContext->getUser();
        $this->data['token'] = $this->securityContext->getToken();
    }

    public function getAuthorizationMatrix(): AuthorizationMatrixInterface
    {
        return $this->data['authorization_matrix'];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
