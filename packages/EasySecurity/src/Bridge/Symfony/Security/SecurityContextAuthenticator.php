<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationExceptionInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Throwable;

final class SecurityContextAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
        private AuthenticationFailureResponseFactoryInterface $responseFactory,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $user = $this->securityContextResolver
                ->resolveContext()
                ->getUserOrFail();
        } catch (Throwable $throwable) {
            if ($throwable instanceof AuthenticationExceptionInterface) {
                throw $throwable;
            }

            throw new AuthenticationException($throwable->getMessage());
        }

        // From here we know we have a user, simply fake it in symfony
        return new SelfValidatingPassport(
            new UserBadge(
                $user->getUserIdentifier(),
                static fn (): UserInterface => $user instanceof UserInterface ? $user : new FakeUser()
            )
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->responseFactory->create($request, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->responseFactory->create($request, $authException);
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }
}
