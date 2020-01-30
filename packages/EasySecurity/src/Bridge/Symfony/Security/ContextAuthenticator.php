<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use EonX\EasySecurity\Interfaces\UserInterface as EonxUserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * ContextAuthenticator constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface $contextResolver
     */
    public function __construct(ContextResolverInterface $contextResolver)
    {
        $this->contextResolver = $contextResolver;
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

        if ($user instanceof EonxUserInterface) {
            return $user;
        }

        // Return void to allow anonymous requests
        return;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => \strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // null will let the request continue
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse(['message' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
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
