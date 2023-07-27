<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\SensitiveData\Fixtures\Dto\ObjectDto;

abstract class AbstractSensitiveDataSanitizerTestCase extends AbstractTestCase
{
    /**
     * @see testSanitize
     */
    public static function providerTestSanitize(): iterable
    {
        yield 'Mask value if key explicitly provided' => [
            'input' => [
                'maskMe' => 'will be masked',
                'a sub-array' => [
                    'maskMeToo' => 'will be masked',
                    'ignoreNull' => null,
                    'ignoreTrue' => true,
                    'ignoreFalse' => false,
                    'ignoreInteger' => 1,
                ],
                'a sub-object (instance of \stdClass)' => \json_decode((string)\json_encode([
                    'maskMeToo' => 'will be masked',
                    'ignoreNull' => null,
                    'ignoreTrue' => true,
                    'ignoreFalse' => false,
                    'ignoreInteger' => 1,
                ])),
                'a sub-object (instance of \DateTimeImmutable)' => new DateTimeImmutable('1970-01-23 01:23:45.123456'),
                'a sub-object (instance of \Carbon\Carbon)' => Carbon::parse(
                    '1970-01-23 01:23:45.123456',
                    'UTC'
                ),
                'a sub-object (instance of \Carbon\CarbonImmutable)' => CarbonImmutable::parse(
                    '1970-01-23 01:23:45.123456',
                    'UTC'
                ),
                'a sub-object (instance of \BpayBillerInfoDto)' => new ObjectDto(
                    'some-biller-code',
                    'some-long-name',
                    'some-short-name'
                ),
            ],
            'expectedOutput' => [
                'maskMe' => '*REDACTED*',
                'a sub-array' => [
                    'maskMeToo' => '*REDACTED*',
                    'ignoreNull' => null,
                    'ignoreTrue' => true,
                    'ignoreFalse' => false,
                    'ignoreInteger' => 1,
                ],
                'a sub-object (instance of \stdClass)' => [
                    'maskMeToo' => '*REDACTED*',
                    'ignoreNull' => null,
                    'ignoreTrue' => true,
                    'ignoreFalse' => false,
                    'ignoreInteger' => 1,
                ],
                'a sub-object (instance of \DateTimeImmutable)' => new DateTimeImmutable('1970-01-23 01:23:45.123456'),
                'a sub-object (instance of \Carbon\Carbon)' => Carbon::parse(
                    '1970-01-23 01:23:45.123456',
                    'UTC'
                ),
                'a sub-object (instance of \Carbon\CarbonImmutable)' => CarbonImmutable::parse(
                    '1970-01-23 01:23:45.123456',
                    'UTC'
                ),
                'a sub-object (instance of \BpayBillerInfoDto)' => [
                    'prop1' => '*REDACTED*',
                    'prop2' => 'some-long-name',
                    'prop3' => 'some-short-name',
                ],
            ],
            'maskKeys' => [
                'prop1',
                'maskme',
                'maskmetoo',
            ],
        ];
        yield 'Mask keys in URL' => [
            'input' => [
                'maskToken' => 'tcp://my-name@yeah?token=token-to-be-masked&PhoneNumber=61000000001&test=1',
            ],
            'expectedOutput' => [
                'maskToken' => 'tcp://my-name@yeah?token=*REDACTED*&PhoneNumber=*REDACTED*&test=1',
            ],
            'maskKeys' => [
                'token',
                'phonenumber',
            ],
        ];
        yield 'Mask keys in JSON' => [
            'input' => [
                'maskToken' => '{"token":"token-to-be-masked"}',
                'maskTokenWithEscaping' => '{\"token\":\"token-to-be-masked\"}',
                'maskTokenSpaceBeforeValue' => '{"token": "token-to-be-masked"}',
                'maskTokenSpaceAfterKey' => '{"token" :"token-to-be-masked"}',
                'maskTokenWithBothSpaces' => '{"token" : "token-to-be-masked"}',
                'maskTokenWithDoubleSpaces' => '{"token"  :  "token-to-be-masked"}',
                'maskTokenSpaceBeforeValueAndEscaping' => '{\"token\": \"token-to-be-masked\"}',
                'maskTokenSpaceAfterKeyAndEscaping' => '{\"token\" :\"token-to-be-masked\"}',
                'maskTokenWithBothSpacesAndEscaping' => '{\"token\" : \"token-to-be-masked\"}',
                'maskTokenWithDoubleSpacesAndEscaping' => '{\"token\"  :  \"token-to-be-masked\"}',
                'maskPhoneNumber' => '{"phoneNumber":"token-to-be-masked"}',
                'maskArray' => '{"auth":["test",null]}',
                'maskArrayWithEscaping' => '{\"auth\":[\"test\"]}',
                'maskArraySpaceBeforeValue' => '{"auth": ["test"]}',
                'maskArraySpaceAfterKey' => '{"auth" :["test"]}',
                'maskArrayWithBothSpaces' => '{"auth" : ["test"]}',
                'maskArrayWithDoubleSpaces' => '{"auth"  :  ["test"]}',
                'maskArraySpaceBeforeValueAndEscaping' => '{\"auth\": [\"test\"]}',
                'maskArraySpaceAfterKeyAndEscaping' => '{\"auth\" :[\"test\"]}',
                'maskArrayWithBothSpacesAndEscaping' => '{\"auth\" : [\"test\"]}',
                'maskArrayWithDoubleSpacesAndEscaping' => '{\"auth\"  :  [\"test\"]}',

            ],
            'expectedOutput' => [
                'maskToken' => '{"token":"*REDACTED*"}',
                'maskTokenWithEscaping' => '{\"token\":\"*REDACTED*\"}',
                'maskTokenSpaceBeforeValue' => '{"token": "*REDACTED*"}',
                'maskTokenSpaceAfterKey' => '{"token" :"*REDACTED*"}',
                'maskTokenWithBothSpaces' => '{"token" : "*REDACTED*"}',
                'maskTokenWithDoubleSpaces' => '{"token"  :  "*REDACTED*"}',
                'maskTokenSpaceBeforeValueAndEscaping' => '{\"token\": \"*REDACTED*\"}',
                'maskTokenSpaceAfterKeyAndEscaping' => '{\"token\" :\"*REDACTED*\"}',
                'maskTokenWithBothSpacesAndEscaping' => '{\"token\" : \"*REDACTED*\"}',
                'maskTokenWithDoubleSpacesAndEscaping' => '{\"token\"  :  \"*REDACTED*\"}',
                'maskPhoneNumber' => '{"phoneNumber":"*REDACTED*"}',
                'maskArray' => '{"auth":[*REDACTED*]}',
                'maskArrayWithEscaping' => '{\"auth\":[*REDACTED*]}',
                'maskArraySpaceBeforeValue' => '{"auth": [*REDACTED*]}',
                'maskArraySpaceAfterKey' => '{"auth" :[*REDACTED*]}',
                'maskArrayWithBothSpaces' => '{"auth" : [*REDACTED*]}',
                'maskArrayWithDoubleSpaces' => '{"auth"  :  [*REDACTED*]}',
                'maskArraySpaceBeforeValueAndEscaping' => '{\"auth\": [*REDACTED*]}',
                'maskArraySpaceAfterKeyAndEscaping' => '{\"auth\" :[*REDACTED*]}',
                'maskArrayWithBothSpacesAndEscaping' => '{\"auth\" : [*REDACTED*]}',
                'maskArrayWithDoubleSpacesAndEscaping' => '{\"auth\"  :  [*REDACTED*]}',
            ],
            'maskKeys' => [
                'token',
                'phonenumber',
                'auth',
            ],
        ];
        yield 'Mask card numbers' => [
            'input' => [
                'withSpace' => '5123 4567 8901 2346',
                'withDoubleSpace' => '5123  4567  8901  2346',
                'noSpace' => '5123456789012346',
                'withDots' => '5123.4567.8901.2346',
                'withSlashes' => '51\234567\890123\46',
                'withinSentenceWithSpace' => 'fewfewkjfewljl 5123 4567 8901 2346 few wfewgew ',
                'withinSentenceNoSpace' => 'fewfewkjfewljl5123456789012346fewwfewgew',
                'Mastercard' => '5313 5810 0012 3430',
                'Visa' => '4005 5500 0000 0001',
                'Amex' => '3456 789012 34564',
                'inJson' => '{"card":"4005 5500 0000 0001"}',
                'inJsonWithEscaping' => '{\"card\":\"4005 5500 0000 0001\"}',
                'inJsonWithSpaceBeforeValue' => '{"card": "4005 5500 0000 0001"}',
                'inJsonWithSpaceAfterKey' => '{"card" :"4005 5500 0000 0001"}',
                'inJsonWithBothSpaces' => '{"card" : "4005 5500 0000 0001"}',
                'inJsonWithDoubleSpaces' => '{"card"  :  "4005 5500 0000 0001"}',
                'inJsonWithSpaceBeforeValueAndEscaping' => '{\"card\": \"4005 5500 0000 0001\"}',
                'inJsonWithSpaceAfterKeyAndEscaping' => '{\"card\" :\"4005 5500 0000 0001\"}',
                'inJsonWithBothSpacesAndEscaping' => '{\"card\" : \"4005 5500 0000 0001\"}',
                'inJsonWithDoubleSpacesAndEscaping' => '{\"card\"  :  \"4005 5500 0000 0001\"}',
                'inUrl' => 'https://eonx.com/page?card=4005 5500 0000 0001',
                'nonCardNumber' => '1234567890123456',
            ],
            'expectedOutput' => [
                'withSpace' => '512345*REDACTED*2346',
                'withDoubleSpace' => '512345*REDACTED*2346',
                'noSpace' => '512345*REDACTED*2346',
                'withDots' => '512345*REDACTED*2346',
                'withSlashes' => '512345*REDACTED*2346',
                'withinSentenceWithSpace' => 'fewfewkjfewljl 512345*REDACTED*2346few wfewgew ',
                'withinSentenceNoSpace' => 'fewfewkjfewljl512345*REDACTED*2346fewwfewgew',
                'Mastercard' => '531358*REDACTED*3430',
                'Visa' => '400555*REDACTED*0001',
                'Amex' => '345678*REDACTED*4564',
                'inJson' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithEscaping' => '{\"card\":\"400555*REDACTED*0001\"}',
                'inJsonWithSpaceBeforeValue' => '{"card": "400555*REDACTED*0001"}',
                'inJsonWithSpaceAfterKey' => '{"card" :"400555*REDACTED*0001"}',
                'inJsonWithBothSpaces' => '{"card" : "400555*REDACTED*0001"}',
                'inJsonWithDoubleSpaces' => '{"card"  :  "400555*REDACTED*0001"}',
                'inJsonWithSpaceBeforeValueAndEscaping' => '{\"card\": \"400555*REDACTED*0001\"}',
                'inJsonWithSpaceAfterKeyAndEscaping' => '{\"card\" :\"400555*REDACTED*0001\"}',
                'inJsonWithBothSpacesAndEscaping' => '{\"card\" : \"400555*REDACTED*0001\"}',
                'inJsonWithDoubleSpacesAndEscaping' => '{\"card\"  :  \"400555*REDACTED*0001\"}',
                'inUrl' => 'https://eonx.com/page?card=400555*REDACTED*0001',
                'nonCardNumber' => '1234567890123456',
            ],
        ];
    }

    /**
     * @param string[]|null $keysToMask
     *
     * @dataProvider providerTestSanitize
     */
    public function testSanitize(array $input, array $expectedOutput, ?array $keysToMask = null): void
    {
        $sanitizer = $this->getSanitizer($keysToMask);

        self::assertEquals($expectedOutput, $sanitizer->sanitize($input));
    }

    abstract protected function getSanitizer(?array $keysToMask = null): SensitiveDataSanitizerInterface;
}
