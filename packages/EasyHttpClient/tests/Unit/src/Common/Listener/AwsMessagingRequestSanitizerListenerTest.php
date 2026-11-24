<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit\Common\Listener;

use ArrayObject;
use Carbon\CarbonImmutable;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\Listener\AwsMessagingRequestSanitizerListener;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
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
                            'Subject' => '*REDACTED*',
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

        yield 'SES e-mail with the json option' => [
            'url' => self::SES_URL,
            'options' => [
                'json' => [
                    'Destination' => [
                        'ToAddresses' => ['john@example.com'],
                    ],
                    'ConfigurationSetName' => 'some-configuration-set',
                ],
            ],
            'expectedOptions' => [
                'json' => [
                    'Destination' => [
                        'ToAddresses' => '*REDACTED*',
                    ],
                    'ConfigurationSetName' => 'some-configuration-set',
                ],
            ],
        ];

        yield 'SES e-mail with header lines in a list' => [
            'url' => self::SES_URL,
            'options' => [
                'headers' => [
                    'Authorization: some-authorization',
                    'Content-Type: application/json',
                    'x-amz-security-token:some-session-token',
                ],
            ],
            'expectedOptions' => [
                'headers' => [
                    'Authorization: *REDACTED*',
                    'Content-Type: application/json',
                    'x-amz-security-token: *REDACTED*',
                ],
            ],
        ];

        yield 'SNS SMS through a FIPS endpoint' => [
            'url' => 'https://sns-fips.us-east-1.amazonaws.com/',
            'options' => [
                'body' => 'Action=Publish&Message=Your+code+is+123456',
            ],
            'expectedOptions' => [
                'body' => 'Action=Publish&Message=*REDACTED*',
            ],
        ];

        yield 'SNS SMS in China' => [
            'url' => 'https://sns.cn-north-1.amazonaws.com.cn/',
            'options' => [
                'body' => 'Action=Publish&Message=Your+code+is+123456',
            ],
            'expectedOptions' => [
                'body' => 'Action=Publish&Message=*REDACTED*',
            ],
        ];

        yield 'SNS SMS with a subject' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'options' => [
                'body' => 'Action=Publish&Message=Your+code+is+123456&MessageStructure=string&Subject=Code+for+John',
            ],
            'expectedOptions' => [
                'body' => 'Action=Publish&Message=*REDACTED*&MessageStructure=string&Subject=*REDACTED*',
            ],
        ];

        yield 'SNS batch with prefixed keys' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'options' => [
                'body' => 'Action=PublishBatch&TopicArn=some-topic'
                    . '&PublishBatchRequestEntries.member.1.Id=1'
                    . '&PublishBatchRequestEntries.member.1.Message=Your+code+is+123456'
                    . '&PublishBatchRequestEntries.member.1.Subject=Code+for+John'
                    . '&PublishBatchRequestEntries.member.1.MessageAttributes.entry.1.Name=some-attribute'
                    . '&PublishBatchRequestEntries.member.2.Id=2'
                    . '&PublishBatchRequestEntries.member.2.Message=Your+code+is+654321',
            ],
            'expectedOptions' => [
                'body' => 'Action=PublishBatch&TopicArn=some-topic'
                    . '&PublishBatchRequestEntries.member.1.Id=1'
                    . '&PublishBatchRequestEntries.member.1.Message=*REDACTED*'
                    . '&PublishBatchRequestEntries.member.1.Subject=*REDACTED*'
                    . '&PublishBatchRequestEntries.member.1.MessageAttributes.entry.1.Name=some-attribute'
                    . '&PublishBatchRequestEntries.member.2.Id=2'
                    . '&PublishBatchRequestEntries.member.2.Message=*REDACTED*',
            ],
        ];

        yield 'SES v1 e-mail with a form body' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => 'Action=SendEmail&Source=no-reply%40example.com'
                    . '&Destination.ToAddresses.member.1=john%40example.com'
                    . '&Message.Subject.Data=Code+for+John'
                    . '&Message.Body.Text.Data=Your+code+is+123456',
            ],
            'expectedOptions' => [
                'body' => 'Action=SendEmail&Source=no-reply%40example.com'
                    . '&Destination.ToAddresses.member.1=*REDACTED*'
                    . '&Message.Subject.Data=*REDACTED*'
                    . '&Message.Body.Text.Data=*REDACTED*',
            ],
        ];

        yield 'SES v1 raw e-mail with a form body' => [
            'url' => self::SES_URL,
            'options' => [
                'body' => 'Action=SendRawEmail&Destinations.member.1=john%40example.com&RawMessage.Data=RnJvbQ%3D%3D',
            ],
            'expectedOptions' => [
                'body' => 'Action=SendRawEmail&Destinations.member.1=*REDACTED*&RawMessage.Data=*REDACTED*',
            ],
        ];

        yield 'json option of another type' => [
            'url' => self::SES_URL,
            'options' => [
                'json' => new ArrayObject([
                    'Destination' => [
                        'ToAddresses' => ['john@example.com'],
                    ],
                ]),
            ],
            'expectedOptions' => [
                'json' => '*REDACTED*',
            ],
        ];

        yield 'null json option' => [
            'url' => self::SES_URL,
            'options' => [
                'json' => null,
            ],
            'expectedOptions' => [
                'json' => null,
            ],
        ];

        yield 'form body with URL-encoded field names' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'options' => [
                'body' => 'Action=Publish&%4Dessage=Your+code+is+123456&Phone%4Eumber=%2B61400000000'
                    . '&PublishBatchRequestEntries%2Emember%2E1%2EMessage=Your+code&Some%4Eame=some%40value',
            ],
            'expectedOptions' => [
                'body' => 'Action=Publish&%4Dessage=*REDACTED*&Phone%4Eumber=*REDACTED*'
                    . '&PublishBatchRequestEntries%2Emember%2E1%2EMessage=*REDACTED*&Some%4Eame=some%40value',
            ],
        ];

        yield 'form body as an array with prefixed keys' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'options' => [
                'body' => [
                    'Action' => 'PublishBatch',
                    'PublishBatchRequestEntries.member.1.Id' => '1',
                    'PublishBatchRequestEntries.member.1.Message' => 'Your code is 123456',
                    'Destination.ToAddresses.member.1' => 'john@example.com',
                ],
            ],
            'expectedOptions' => [
                'body' => [
                    'Action' => 'PublishBatch',
                    'PublishBatchRequestEntries.member.1.Id' => '1',
                    'PublishBatchRequestEntries.member.1.Message' => '*REDACTED*',
                    'Destination.ToAddresses.member.1' => '*REDACTED*',
                ],
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

    /**
     * @see testItRedactsErrorMessagesOfResponses
     */
    public static function provideResponses(): iterable
    {
        yield 'SES error' => [
            'url' => self::SES_URL,
            'content' => '{"message":"Email address is not verified. The following identities failed the check'
                . ' in region AP-SOUTHEAST-2: john@example.com"}',
            'expectedContent' => '{"message":"*REDACTED*"}',
        ];

        yield 'SNS error' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'content' => '<ErrorResponse><Error><Type>Sender</Type><Code>InvalidParameter</Code>'
                . '<Message>Invalid parameter: PhoneNumber Reason: +61400000000 is not valid to publish to</Message>'
                . '</Error><RequestId>some-request-id</RequestId></ErrorResponse>',
            'expectedContent' => '<ErrorResponse><Error><Type>Sender</Type><Code>InvalidParameter</Code>'
                . '<Message>*REDACTED*</Message></Error><RequestId>some-request-id</RequestId></ErrorResponse>',
        ];

        yield 'truncated SES error' => [
            'url' => self::SES_URL,
            'content' => '{"message":"Unknown recipient john@example.com',
            'expectedContent' => '*REDACTED*',
        ];

        yield 'truncated SNS error' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'content' => '<ErrorResponse><Error><Code>InvalidParameter</Code><Message>Invalid parameter: +6140',
            'expectedContent' => '<ErrorResponse><Error><Code>InvalidParameter</Code><Message>*REDACTED*</Message>',
        ];

        yield 'plain text error' => [
            'url' => self::SES_URL,
            'content' => 'Unknown recipient john@example.com',
            'expectedContent' => '*REDACTED*',
        ];

        yield 'empty response' => [
            'url' => self::SES_URL,
            'content' => '',
            'expectedContent' => '',
        ];

        yield 'SES success' => [
            'url' => self::SES_URL,
            'content' => '{"MessageId":"some-message-id"}',
            'expectedContent' => '{"MessageId":"some-message-id"}',
        ];

        yield 'SNS success' => [
            'url' => 'https://sns.ap-southeast-2.amazonaws.com/',
            'content' => '<PublishResponse><PublishResult><MessageId>some-message-id</MessageId></PublishResult>'
                . '</PublishResponse>',
            'expectedContent' => '<PublishResponse><PublishResult><MessageId>some-message-id</MessageId>'
                . '</PublishResult></PublishResponse>',
        ];

        yield 'not an AWS host' => [
            'url' => 'https://api.example.com/messages',
            'content' => '{"message":"Unknown recipient john@example.com"}',
            'expectedContent' => '{"message":"Unknown recipient john@example.com"}',
        ];
    }

    #[DataProvider('provideRequestsToKeep')]
    public function testItKeepsRequestOptions(string $url, array $options): void
    {
        $requestData = self::createRequestData($url, $options);

        (new AwsMessagingRequestSanitizerListener())(new HttpRequestSentEvent($requestData));

        self::assertSame($options, $requestData->getOptions());
    }

    #[DataProvider('provideResponses')]
    public function testItRedactsErrorMessagesOfResponses(string $url, string $content, string $expectedContent): void
    {
        $responseData = new ResponseData(
            content: $content,
            headers: [],
            receivedAt: CarbonImmutable::parse('2026-01-01 00:00:01'),
            statusCode: 400
        );

        (new AwsMessagingRequestSanitizerListener())(new HttpRequestSentEvent(
            self::createRequestData($url, []),
            $responseData
        ));

        self::assertSame($expectedContent, $responseData->getContent());
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
