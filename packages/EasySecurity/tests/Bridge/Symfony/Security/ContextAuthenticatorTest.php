<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Security\ContextAuthenticator;
use EonX\EasySecurity\Configurators\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasySecurity\Tests\Bridge\Symfony\Stubs\SymfonyUserStub;
use Symfony\Component\HttpFoundation\Request;

final class ContextAuthenticatorTest extends AbstractSymfonyTestCase
{
    public function testSanityCheck(): void
    {
        $container = $this->getKernel()->getContainer();
        $request = new Request([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]);
        $user = new SymfonyUserStub();

        $contextAuthenticator = $container->get(ContextAuthenticator::class);
        $context = $contextAuthenticator->getCredentials($request);

        self::assertTrue($contextAuthenticator->checkCredentials([], $user));
        self::assertTrue($contextAuthenticator->supports($request));
        self::assertFalse($contextAuthenticator->supportsRememberMe());
        self::assertInstanceOf(SecurityContextInterface::class, $context);
    }
}
