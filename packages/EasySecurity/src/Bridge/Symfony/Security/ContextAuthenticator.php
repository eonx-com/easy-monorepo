<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ContextResolverInterface;
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
     * @var \EonX\EasySecurity\Interfaces\ContextResolverInterface
     */
    private $contextResolver;

    /**
     * @var \EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * ContextAuthenticator constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextResolverInterface $contextResolver
     * @param \EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface $respFactory
     */
    public function __construct(
        ContextResolverInterface $contextResolver,
        AuthenticationFailureResponseFactoryInterface $respFactory
    ) {
        $this->contextResolver = $contextResolver;
        $this->responseFactory = $respFactory;
    }

    /**
     * Check credentials.
     *
     * @param mixed $credentials
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    /**
     * Get credentials.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
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

    /**
     * Create response on authentication failure.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->responseFactory->create($request, $exception);
    }

    /**
     * Create response on authentication success.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $providerKey
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null; // null will let the request continue
    }

    /**
     * Create response to start authentication process.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param null|\Symfony\Component\Security\Core\Exception\AuthenticationException $authException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->responseFactory->create($request, $authException);
    }

    /**
     * Check if given request is supported.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return true;
    }

    /**
     * Define if remember me feature is supported.
     *
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}
