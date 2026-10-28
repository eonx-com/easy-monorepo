<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Listener;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasySecurity\SymfonySecurity\Listener\AddWwwAuthenticateHeaderListener;
use EonX\EasySecurity\SymfonySecurity\Resolver\WwwAuthenticateResolver;
use EonX\EasySecurity\Tests\Stub\Decoder\TokenDecoderStub;
use EonX\EasySecurity\Tests\Stub\Factory\ApiTokenDecoderFactoryStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class AddWwwAuthenticateHeaderListenerTest extends AbstractUnitTestCase
{
    /**
     * @see testInvokeSucceeds
     */
    public static function provideInvokeData(): iterable
    {
        yield 'Unauthorized response gets the challenge' => [
            'response' => new Response(status: Response::HTTP_UNAUTHORIZED),
            'requestType' => HttpKernelInterface::MAIN_REQUEST,
            'expectedHeader' => 'Basic realm="api-key"',
        ];

        yield 'Existing header is kept' => [
            'response' => new Response(status: Response::HTTP_UNAUTHORIZED, headers: [
                'WWW-Authenticate' => 'Custom',
            ]),
            'requestType' => HttpKernelInterface::MAIN_REQUEST,
            'expectedHeader' => 'Custom',
        ];

        yield 'Other status code is ignored' => [
            'response' => new Response(status: Response::HTTP_FORBIDDEN),
            'requestType' => HttpKernelInterface::MAIN_REQUEST,
            'expectedHeader' => null,
        ];

        yield 'Sub request is ignored' => [
            'response' => new Response(status: Response::HTTP_UNAUTHORIZED),
            'requestType' => HttpKernelInterface::SUB_REQUEST,
            'expectedHeader' => null,
        ];
    }

    #[DataProvider('provideInvokeData')]
    public function testInvokeSucceeds(Response $response, int $requestType, ?string $expectedHeader): void
    {
        /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $kernel */
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $event = new ResponseEvent($kernel, new Request(), $requestType, $response);
        $sut = new AddWwwAuthenticateHeaderListener(new WwwAuthenticateResolver(
            'Bearer',
            new ApiTokenDecoderFactoryStub(new TokenDecoderStub(new ApiKey('api-key')))
        ));

        $sut($event);

        self::assertSame($expectedHeader, $response->headers->get('WWW-Authenticate'));
    }
}
