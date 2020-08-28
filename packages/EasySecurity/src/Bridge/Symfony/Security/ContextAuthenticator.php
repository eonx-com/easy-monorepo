<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\UserInterface as EonxUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class ContextAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var \EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationFailureResponseFactoryInterface $respFactory
    ) {
        $this->securityContext = $securityContext;
        $this->responseFactory = $respFactory;
    }

    /**
     * @param mixed $credentials
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function getCredentials(Request $request): ContextInterface
    {
        return $this->securityContext;
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     *
     * @return null|void|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser($context, UserProviderInterface $userProvider)
    {
        $user = $context->getUser();

        return $user instanceof EonxUserInterface ? $user : null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->responseFactory->create($request, $exception);
    }

    /**
     * @param string $providerKey
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null; // null will let the request continue
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->responseFactory->create($request, $authException);
    }

    public function supports(Request $request): bool
    {
        return true;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
