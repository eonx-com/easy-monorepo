<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Laravel;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\SecurityContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class EasySecurityServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication(null, [
            'easy-api-token' => [
                'decoders' => [
                    'basic' => null,
                ],
            ],
            'easy-security' => [
                'token_decoder' => 'basic',
            ],
        ]);

        $app->instance(Request::class, new SymfonyRequest([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]));

        self::assertInstanceOf(SecurityContext::class, $app->make(SecurityContextInterface::class));
    }
}
