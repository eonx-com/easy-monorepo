<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
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
     * @var \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface
     */
    private $contextResolver;

    /**
     * @var \EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface
     */
    private $failureResponseFactory;

    /**
     * ContextAuthenticator constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface $contextResolver
     * @param \EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface $failureResponseFactory
     */
    public function __construct(
        ContextResolverInterface $contextResolver,
        AuthenticationFailureResponseFactoryInterface $failureResponseFactory
    ) {
        $this->contextResolver = $contextResolver;
        $this->failureResponseFactory = $failureResponseFactory;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function getCredentials(Request $request): ContextInterface
    {
        return $this->contextResolver->resolve($request);
    }

    /**
     * Get user.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     *
     * @return null|void|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser($context, UserProviderInterface $userProvider)
    {
        $user = $context->getUser();

        return $user instanceof EonxUserInterface ? $user : null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->failureResponseFactory->create($request, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null; // null will let the request continue
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->failureResponseFactory->create($request, $authException);
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
