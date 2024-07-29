<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit\EasyBugsnag\Listener;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Carbon\CarbonImmutable;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use EonX\EasyHttpClient\EasyBugsnag\Listener\HttpRequestSentBreadcrumbListener;
use EonX\EasyHttpClient\Tests\Unit\AbstractUnitTestCase;
use Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class HttpRequestSentBreadcrumbListenerTest extends AbstractUnitTestCase
{
    /**
     * @see testPrepareMetadataSucceeds
     */
    public static function provideEvents(): iterable
    {
        yield 'an event with response data' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                responseData: new ResponseData(
                    content: 'some-content',
                    headers: ['headerName' => 'header-value'],
                    receivedAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456'),
                    statusCode: 200
                )
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Request Options' => '{"optionName":"option-value"}',
                'Response Status Code' => 200,
                'Response Content' => 'some-content',
                'Response Headers' => '{"headerName":"header-value"}',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with a throwable and the throwableThrownAt value' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                throwable: new Exception('some-exception-message'),
                throwableThrownAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456')
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Request Options' => '{"optionName":"option-value"}',
                'Throwable Class' => 'Exception',
                'Throwable Message' => 'some-exception-message',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with a throwable and without the throwableThrownAt value' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                throwable: new Exception('some-exception-message')
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Request Options' => '{"optionName":"option-value"}',
                'Throwable Class' => 'Exception',
                'Throwable Message' => 'some-exception-message',
                'Timing' => 'No timing available',
            ],
        ];

        yield 'an event with response data, the response headers value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                responseData: new ResponseData(
                    content: 'some-content',
                    headers: \array_fill(0, Breadcrumb::MAX_SIZE, '0'),
                    receivedAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456'),
                    statusCode: 200
                )
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Request Options' => '{"optionName":"option-value"}',
                'Response Status Code' => 200,
                'Response Content' => 'some-content',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with response data, the request options value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: \array_fill(0, Breadcrumb::MAX_SIZE, '0'),
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                responseData: new ResponseData(
                    content: 'some-content',
                    headers: ['headerName' => 'header-value'],
                    receivedAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456'),
                    statusCode: 200
                )
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Response Status Code' => 200,
                'Response Content' => 'some-content',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with response data, the response content value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                responseData: new ResponseData(
                    content: \str_pad('0', Breadcrumb::MAX_SIZE),
                    headers: ['headerName' => 'header-value'],
                    receivedAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456'),
                    statusCode: 200
                )
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Response Status Code' => 200,
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with response data, the request value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: \str_pad('0', Breadcrumb::MAX_SIZE)
                ),
                responseData: new ResponseData(
                    content: 'some-content',
                    headers: ['headerName' => 'header-value'],
                    receivedAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456'),
                    statusCode: 200
                )
            ),
            'expectedMetadata' => [],
        ];

        yield 'an event with a throwable, the request options value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: \array_fill(0, Breadcrumb::MAX_SIZE, '0'),
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                throwable: new Exception('some-exception-message'),
                throwableThrownAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456')
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Throwable Class' => 'Exception',
                'Throwable Message' => 'some-exception-message',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with a throwable, the throwable message value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: 'http://example.test/foo'
                ),
                throwable: new Exception(\str_pad('0', Breadcrumb::MAX_SIZE)),
                throwableThrownAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456')
            ),
            'expectedMetadata' => [
                'Request' => 'GET - http://example.test/foo',
                'Timing' => '08/08/2022 12:34:56.789123 - 08/08/2022 12:34:59.789456 | 3s after',
            ],
        ];

        yield 'an event with a throwable, the request value is too long' => [
            'event' => new HttpRequestSentEvent(
                requestData: new RequestData(
                    method: 'GET',
                    options: ['optionName' => 'option-value'],
                    sentAt: CarbonImmutable::parse('08.08.2022 12:34:56.789123'),
                    url: \str_pad('0', Breadcrumb::MAX_SIZE),
                ),
                throwable: new Exception('some-exception-message'),
                throwableThrownAt: CarbonImmutable::parse('08.08.2022 12:34:59.789456')
            ),
            'expectedMetadata' => [],
        ];
    }

    #[DataProvider('provideEvents')]
    public function testPrepareMetadataSucceeds(HttpRequestSentEvent $event, array $expectedMetadata): void
    {
        $bugsnagClient = $this->mockBugsnagClient($expectedMetadata);
        $sut = new HttpRequestSentBreadcrumbListener($bugsnagClient);

        $sut($event);
    }

    private function mockBugsnagClient(array $expectedMetadata): Client
    {
        /** @var \Bugsnag\Client $bugsnagClient */
        $bugsnagClient = $this->mock(
            Client::class,
            static function (MockInterface $mock) use ($expectedMetadata): void {
                $mock->shouldReceive('leaveBreadcrumb')
                    ->once()
                    ->with(
                        'HTTP Request Sent',
                        Breadcrumb::REQUEST_TYPE,
                        $expectedMetadata
                    );
            }
        );

        return $bugsnagClient;
    }
}
