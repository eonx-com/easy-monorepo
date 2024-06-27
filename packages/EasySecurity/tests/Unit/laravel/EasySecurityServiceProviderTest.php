<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Laravel;

use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasySecurity\Common\Configurator\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class EasySecurityServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication(null, [
            'easy-security' => [
                'token_decoder' => BasicAuthDecoder::class,
            ],
        ]);

        $app->instance(Request::class, new SymfonyRequest([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]));
        /** @var \EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface $result */
        $result = $app->make(SecurityContextResolverInterface::class)
            ->setConfigurator(new DefaultSecurityContextConfigurator());

        self::assertInstanceOf(SecurityContext::class, $result->resolveContext());
    }
}
