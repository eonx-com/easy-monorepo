<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Listeners;

use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Bridge\Symfony\Listeners\ReadListener;
use EonX\EasyApiPlatform\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource\Dummy;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ReadListenerTest extends AbstractSymfonyTestCase
{
    /**
     * @see testItThrowsException
     */
    public static function provideRequestsForCasesWithThrownException(): iterable
    {
        yield 'a request with operation with URI variables' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new Post(uriVariables: ['id' => new Link()]),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => true,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request with method safe equals to true' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new Post(uriVariables: []),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => true,
                ],
                server: [
                    'REQUEST_METHOD' => 'GET',
                ]
            ),
        ];
    }

    /**
     * @see testItSucceeds
     */
    public static function provideRequestsForSuccessfulCases(): iterable
    {
        yield 'a request without attributes' => [
            'request' => new Request(
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request without "receive" attribute' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new Post(uriVariables: []),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request with "receive" attribute equals to "false"' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new Post(uriVariables: []),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => false,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request without operation' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => null,
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => true,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request with operation that cannot read' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new NotExposed(uriVariables: []),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => true,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];

        yield 'a request with method safe equals to false and with operation without URI variables' => [
            'request' => new Request(
                attributes: [
                    '_api_operation' => new Post(uriVariables: []),
                    '_api_operation_name' => 'post_dummy',
                    '_api_resource_class' => Dummy::class,
                    'receive' => true,
                ],
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            ),
        ];
    }

    #[DataProvider('provideRequestsForSuccessfulCases')]
    public function testItSucceeds(Request $request): void
    {
        $event = new RequestEvent($this->getKernel(), $request, HttpKernelInterface::MAIN_REQUEST);
        $sut = new ReadListener();

        $sut($event);

        self::assertTrue(true);
    }

    #[DataProvider('provideRequestsForCasesWithThrownException')]
    public function testItThrowsException(Request $request): void
    {
        $event = new RequestEvent($this->getKernel(), $request, HttpKernelInterface::MAIN_REQUEST);
        $sut = new ReadListener();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Not Found');

        $sut($event);
    }
}
