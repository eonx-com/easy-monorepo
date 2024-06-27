<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Authenticator;

use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\SymfonySecurity\Authenticator\SecurityContextAuthenticator;
use EonX\EasySecurity\SymfonySecurity\Factory\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\Tests\Stub\Exception\CustomAuthenticationException;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class SecurityContextAuthenticatorTest extends AbstractUnitTestCase
{
    /**
     * @see testAuthenticateThrowsCorrectException
     */
    public static function provideExceptions(): iterable
    {
        yield 'Library exception 1' => [
            'thrownException' => new RuntimeException(),
            'expectedExceptionClass' => AuthenticationException::class,
        ];

        yield 'Library exception 2' => [
            'thrownException' => new LogicException(),
            'expectedExceptionClass' => AuthenticationException::class,
        ];

        yield 'Custom exception' => [
            'thrownException' => new CustomAuthenticationException(),
            'expectedExceptionClass' => CustomAuthenticationException::class,
        ];
    }

    /**
     * @psalm-param class-string<\Throwable> $expectedExceptionClass
     */
    #[DataProvider('provideExceptions')]
    public function testAuthenticateThrowsCorrectException(
        Throwable $thrownException,
        string $expectedExceptionClass,
    ): void {
        $this->expectException($expectedExceptionClass);

        $securityContextResolver = $this->prophesize(SecurityContextResolverInterface::class);
        $securityContextResolver->resolveContext()
            ->willThrow($thrownException);
        /** @var \EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface $securityContextResolverReveal */
        $securityContextResolverReveal = $securityContextResolver->reveal();
        $authenticator = new SecurityContextAuthenticator(
            $securityContextResolverReveal,
            new AuthenticationFailureResponseFactory()
        );

        $authenticator->authenticate(new Request());
    }
}
