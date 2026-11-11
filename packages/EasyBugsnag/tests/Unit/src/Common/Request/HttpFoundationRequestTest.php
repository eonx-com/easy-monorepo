<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Request;

use EonX\EasyBugsnag\Common\Request\HttpFoundationRequest;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class HttpFoundationRequestTest extends AbstractUnitTestCase
{
    /**
     * @see testGetMetaDataContent
     */
    public static function provideContentData(): iterable
    {
        yield 'json body is decoded' => [
            'request' => self::createRequest('{"password":"secret"}', 'application/json'),
            'expectedContent' => [
                'content' => (object)[
                    'password' => 'secret',
                ],
            ],
        ];
        yield 'malformed json body is kept as text' => [
            'request' => self::createRequest('{"password":', 'application/json'),
            'expectedContent' => [
                'content' => '{"password":',
            ],
        ];
        yield 'form body without parsed fields is not sent' => [
            'request' => self::createRequest('user%5Bpassword%5D=secret', 'application/x-www-form-urlencoded'),
            'expectedContent' => [],
        ];
        yield 'multipart body without parsed fields is not sent' => [
            'request' => self::createRequest('--b\r\npassword=secret', 'multipart/form-data; boundary=b'),
            'expectedContent' => [],
        ];
        yield 'short text body is kept' => [
            'request' => self::createRequest('<password>secret</password>', 'application/xml'),
            'expectedContent' => [
                'content' => '<password>secret</password>',
            ],
        ];
        yield 'long json body with a non-json content type is decoded' => [
            'request' => self::createRequest(
                '{"password":"secret","padding":"' . \str_repeat('a', 10_240) . '"}',
                'text/plain'
            ),
            'expectedContent' => [
                'content' => (object)[
                    'password' => 'secret',
                    'padding' => \str_repeat('a', 10_240),
                ],
            ],
        ];
        yield 'long text body is not sent' => [
            'request' => self::createRequest(
                \str_repeat('a', 10_230) . ' {"password":"secret","card":"4111 1111 1111 1111"}',
                'text/plain'
            ),
            'expectedContent' => [
                'content_length' => 10_281,
                'content_omitted' => true,
            ],
        ];
        yield 'text body of exactly 10 KB is kept' => [
            'request' => self::createRequest(\str_repeat('a', 10_240), 'text/plain'),
            'expectedContent' => [
                'content' => \str_repeat('a', 10_240),
            ],
        ];
    }

    #[DataProvider('provideContentData')]
    public function testGetMetaDataContent(Request $request, array $expectedContent): void
    {
        $metaData = (new HttpFoundationRequest($request))->getMetaData();

        $content = \array_intersect_key($metaData['request'], [
            'content' => true,
            'content_length' => true,
            'content_omitted' => true,
        ]);
        self::assertEquals($expectedContent, $content);
        self::assertArrayNotHasKey('request', $metaData['request']);
    }

    public function testGetMetaDataSendsParsedFormFieldsWithoutRawBody(): void
    {
        $request = Request::create('/login', 'POST', [
            'user' => [
                'password' => 'secret',
            ],
        ]);

        $metaData = (new HttpFoundationRequest($request))->getMetaData();

        self::assertArrayNotHasKey('content', $metaData['request']);
        self::assertSame([
            'user' => [
                'password' => 'secret',
            ],
        ], $metaData['request']['request']);
    }

    public function testGetSessionSkipsSecurityAndCsrfTokens(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('_security_main', 'O:74:"Symfony\Component\Security\Core\Authentication\Token\..."');
        $session->set('_csrf/authenticate', 'csrf-token');
        $session->set('_locale', 'en');
        $session->set('cart_id', 'abc');
        $request = Request::create('/');
        $request->setSession($session);

        $sessionData = (new HttpFoundationRequest($request))->getSession();

        self::assertSame([
            '_locale' => 'en',
            'cart_id' => 'abc',
        ], $sessionData);
    }

    private static function createRequest(string $content, string $contentType): Request
    {
        return Request::create(
            uri: '/test',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => $contentType,
            ],
            content: $content
        );
    }
}
