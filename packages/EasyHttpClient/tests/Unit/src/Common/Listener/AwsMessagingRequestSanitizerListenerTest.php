<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit\Common\Listener;

use Carbon\CarbonImmutable;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\Listener\AwsMessagingRequestSanitizerListener;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class AwsMessagingRequestSanitizerListenerTest extends AbstractUnitTestCase
{
    private const SES_URL = 'https://email.ap-southeast-2.amazonaws.com/v2/email/outbound-emails';

    /**
     * @see testItKeepsRequestOptions
     */
    public static function provideRequestsToKeep(): iterable
    {
        $options = [
            'body' => '{"Destination":{"ToAddresses":["john@example.com"]},"Message":"Your code is 123456"}',
            'headers' => [
                'Authorization' => 'some-authorization',
            ],
        ];

        yield 'not an AWS host' => [
            'url' => 'https://api.example.com/messages',
            'options' => $options,
        ];

        yield 'another AWS service' => [
            'url' => 'https://sqs.ap-southeast-2.amazonaws.com/',
            'options' => $options,
        ];

        yield 'host that only starts like an AWS host' => [
            'url' => 'https://email.ap-southeast-2.amazonaws.com.example.com/v2/email/outbound-emails',
            'options' => $options,
        ];
    }

    /**
     * @see testItRedactsMessageContentAndCredentials
     */
    public static function provideRequestsToRedact(): iterable
    {
        yield 'SES e-mail with a JSON body' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => \json_encode([
                    'FromEmailAddress' => 'no-reply@example.com',
                    'Destination' => [
                        'ToAddresses' => ['john@example.com'],
                        'CcAddresses' => ['jane@example.com'],
                        'BccAddresses' => ['audit@example.com'],
                    ],
                    'ReplyToAddresses' => ['support@example.com'],
                    'Content' => [
                        'Simple' => [
                            'Subject' => [
                                'Data' => 'Authentication code for John Smith',
                            ],
                            'Body' => [
                                'Text' => [
                                    'Data' => 'Your code is 123456',
                                ],
                                'Html' => [
                                    'Data' => '<p>Your code is 123456</p>',
                                ],
                            ],
                            'Attachments' => [
                                [
                                    'FileName' => 'statement.pdf',
                                    'RawContent' => 'c3RhdGVtZW50',
                                ],
                            ],
                        ],
                    ],
                    'ConfigurationSetName' => 'some-configuration-set',
                ]),
                'headers' => [
                    'Authorization' => 'some-authorization',
                    'Content-Type' => 'application/json',
                    'X-Amz-Security-Token' => 'some-session-token',
                ],
            ],
            'expectedOptions' => [
                'body' => \json_encode([
                    'FromEmailAddress' => 'no-reply@example.com',
                    'Destination' => [
                        'ToAddresses' => '*REDACTED*',
                        'CcAddresses' => '*REDACTED*',
                        'BccAddresses' => '*REDACTED*',
                    ],
                    'ReplyToAddresses' => '*REDACTED*',
                    'Content' => [
                        'Simple' => [
                            'Subject' => [
                                'Data' => '*REDACTED*',
                            ],
                            'Body' => [
                                'Text' => [
                                    'Data' => '*REDACTED*',
                                ],
                                'Html' => [
                                    'Data' => '*REDACTED*',
                                ],
                            ],
                            'Attachments' => [
                                [
                                    'FileName' => 'statement.pdf',
                                    'RawContent' => '*REDACTED*',
                                ],
                            ],
                        ],
                    ],
                    'ConfigurationSetName' => 'some-configuration-set',
                ]),
                'headers' => [
                    'Authorization' => '*REDACTED*',
                    'Content-Type' => 'application/json',
                    'X-Amz-Security-Token' => '*REDACTED*',
                ],
            ],
        ];

        yield 'SES e-mail with an array body and header lists' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => [
                    'Destination' => [
                        'ToAddresses' => ['john@example.com'],
                    ],
                    'Content' => [
                        'Simple' => [
                            'Body' => [
                                'Text' => [
                                    'Data' => 'Your code is 123456',
                                ],
                            ],
                        ],
                    ],
                ],
                'headers' => [
                    'x-amz-security-token' => ['some-session-token', 'another-session-token'],
                ],
            ],
            'expectedOptions' => [
                'body' => [
                    'Destination' => [
                        'ToAddresses' => '*REDACTED*',
                    ],
                    'Content' => [
                        'Simple' => [
                            'Body' => [
                                'Text' => [
                                    'Data' => '*REDACTED*',
                                ],
                            ],
                        ],
                    ],
                ],
                'headers' => [
                    'x-amz-security-token' => ['*REDACTED*', '*REDACTED*'],
                ],
            ],
        ];

        yield 'SES raw e-mail' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => '{"Content":{"Raw":{"Data":"RnJvbTogbm8tcmVwbHk="}}}',
            ],
            'expectedOptions' => [
                'body' => '{"Content":{"Raw":{"Data":"*REDACTED*"}}}',
            ],
        ];

        yield 'SES template e-mail in another region' => [
            'url' => 'https://email.us-east-1.amazonaws.com/v2/email/outbound-emails',
            'options' => [
                'body' => '{"Content":{"Template":{"TemplateName":"mfa","TemplateData":"{\"code\":\"123456\"}"}}}',
            ],
            'expectedOptions' => [
                'body' => '{"Content":{"Template":{"TemplateName":"mfa","TemplateData":"*REDACTED*"}}}',
            ],
        ];

        yield 'SNS SMS with a form body' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'options' => [
                'body' => 'Action=Publish&Version=2010-03-31&Message=Your+code+is+123456&PhoneNumber=%2B61400000000'
                    . '&MessageAttributes.entry.1.Name=AWS.SNS.SMS.SenderID&SomeMessage=kept',
            ],
            'expectedOptions' => [
                'body' => 'Action=Publish&Version=2010-03-31&Message=*REDACTED*&PhoneNumber=*REDACTED*'
                    . '&MessageAttributes.entry.1.Name=AWS.SNS.SMS.SenderID&SomeMessage=kept',
            ],
        ];

        yield 'SES e-mail with a relative URL and the base URI option' => [
            'url' => '/v2/email/outbound-emails',
            'options' => [
                'base_uri' => 'https://email.ap-southeast-2.amazonaws.com',
                'body' => '{"Destination":{"ToAddresses":["john@example.com"]}}',
            ],
            'expectedOptions' => [
                'base_uri' => 'https://email.ap-southeast-2.amazonaws.com',
                'body' => '{"Destination":{"ToAddresses":"*REDACTED*"}}',
            ],
        ];

        yield 'body of an unsupported type' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => new stdClass(),
            ],
            'expectedOptions' => [
                'body' => '*REDACTED*',
            ],
        ];
    }

    #[DataProvider('provideRequestsToKeep')]
    public function testItKeepsRequestOptions(string $url, array $options): void
    {
        $requestData = self::createRequestData($url, $options);

        (new AwsMessagingRequestSanitizerListener())(new HttpRequestSentEvent($requestData));

        self::assertSame($options, $requestData->getOptions());
    }

    #[DataProvider('provideRequestsToRedact')]
    public function testItRedactsMessageContentAndCredentials(string $url, array $options, array $expectedOptions): void
    {
        $requestData = self::createRequestData($url, $options);

        (new AwsMessagingRequestSanitizerListener())(new HttpRequestSentEvent($requestData));

        self::assertSame($expectedOptions, $requestData->getOptions());
    }

    private static function createRequestData(string $url, array $options): RequestData
    {
        return new RequestData(
            method: 'POST',
            options: $options,
            sentAt: CarbonImmutable::parse('2026-01-01 00:00:00'),
            url: $url
        );
    }
}
