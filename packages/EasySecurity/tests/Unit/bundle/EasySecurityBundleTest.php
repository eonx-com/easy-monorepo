<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Bundle;

use EonX\EasySecurity\Common\Configurator\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\EasyBugsnag\Configurator\SecurityContextClientConfigurator;
use EonX\EasySecurity\SymfonySecurity\Controller\EasySecurityLoginController;
use EonX\EasySecurity\SymfonySecurity\Listener\AddWwwAuthenticateHeaderListener;
use EonX\EasySecurity\Tests\Unit\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;

final class EasySecurityBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testWwwAuthenticateHeaderIsSent
     */
    public static function provideWwwAuthenticateData(): iterable
    {
        yield 'Default challenge without Authorization header' => [
            'authorization' => null,
            'expectedChallenge' => 'Bearer',
        ];

        yield 'Challenge from the configured decoder' => [
            'authorization' => 'Basic ' . \base64_encode('username:password'),
            'expectedChallenge' => 'Basic realm="user"',
        ];
    }

    public function testLoginRouteIsImportedFromRoutesConfig(): void
    {
        $fileLocator = new FileLocator();
        $attributeLoader = new AttributeRouteControllerLoader();
        $loader = new PhpFileLoader($fileLocator);
        $loader->setResolver(new LoaderResolver([
            $loader,
            new AttributeDirectoryLoader($fileLocator, $attributeLoader),
        ]));

        $routes = $loader->load(__DIR__ . '/../../../bundle/config/routes.php');

        $route = $routes->get('easy_security.login');
        self::assertCount(1, $routes);
        self::assertNotNull($route);
        self::assertSame('/easy-security/login', $route->getPath());
        self::assertSame(EasySecurityLoginController::class . '::login', $route->getDefault('_controller'));
    }

    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default.php'])
            ->getContainer();

        /** @var \EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface $result */
        $result = $container->get(SecurityContextResolverInterface::class)
            ->setConfigurator(new DefaultSecurityContextConfigurator());

        self::assertInstanceOf(SecurityContextInterface::class, $result->resolveContext());
        self::assertTrue($container->has(SecurityContextClientConfigurator::class));
    }

    public function testWwwAuthenticateHeaderBlankDefaultChallengeThrowsInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "easy_security.www_authenticate_header.default_challenge":'
            . ' The default challenge must not be blank.'
        );

        $this->getKernel([__DIR__ . '/../../Fixture/config/www_authenticate_header_blank_default_challenge.php']);
    }

    public function testWwwAuthenticateHeaderIsNotSentWhenDisabled(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/www_authenticate_header_disabled.php'])
            ->getContainer();

        self::assertFalse($container->has(AddWwwAuthenticateHeaderListener::class));
    }

    #[DataProvider('provideWwwAuthenticateData')]
    public function testWwwAuthenticateHeaderIsSent(?string $authorization, string $expectedChallenge): void
    {
        $kernel = $this->getKernel([__DIR__ . '/../../Fixture/config/default.php']);
        $request = new Request();
        if ($authorization !== null) {
            $request->headers->set('Authorization', $authorization);
        }
        $response = new Response(status: Response::HTTP_UNAUTHORIZED);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        /** @var \EonX\EasySecurity\SymfonySecurity\Listener\AddWwwAuthenticateHeaderListener $sut */
        $sut = $kernel->getContainer()
            ->get(AddWwwAuthenticateHeaderListener::class);

        $sut($event);

        self::assertSame($expectedChallenge, $response->headers->get('WWW-Authenticate'));
    }
}
