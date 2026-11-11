<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\Fixture\SensitiveData\DummyObject;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

abstract class AbstractSensitiveDataSanitizerTestCase extends AbstractUnitTestCase
{
    /**
     * @see testSanitize
     */
    public static function provideAbstractDataForSanitizing(): iterable
    {
        yield 'Mask value if key explicitly provided' => [
            'input' => [
                0 => 'will be masked',
                'ignoreBool' => true,
                'ignoreDouble' => 1.23,
                'ignoreInteger' => 1,
                'ignoreNull' => null,
                'ignoreString' => 'will not be masked',
                'maskBoolean' => true,
                'maskDouble' => 1.23,
                'maskInteger' => 1,
                'maskNull' => null,
                'maskString' => 'will be masked',
                'a sub-array' => [
                    'ignoreBool' => true,
                    'ignoreDouble' => 1.23,
                    'ignoreInteger' => 1,
                    'ignoreNull' => null,
                    'ignoreString' => 'will not be masked',
                    'maskBoolean' => true,
                    'maskDouble' => 1.23,
                    'maskInteger' => 1,
                    'maskNull' => null,
                    'maskString' => 'will be masked',
                ],
                'a sub-object (instance of \stdClass)' => \json_decode((string)\json_encode([
                    'ignoreBool' => true,
                    'ignoreDouble' => 1.23,
                    'ignoreInteger' => 1,
                    'ignoreNull' => null,
                    'ignoreString' => 'will not be masked',
                    'maskBoolean' => true,
                    'maskDouble' => 1.23,
                    'maskInteger' => 1,
                    'maskNull' => null,
                    'maskString' => 'will be masked',
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
                'a sub-object (instance of \EonX\EasyUtils\Tests\SensitiveData\Fixtures\Dto\ObjectDto)' =>
                    new DummyObject(
                        prop1: 'some-biller-code',
                        prop2: 'some-long-name',
                        prop3: 'some-short-name'
                    ),
            ],
            'expectedOutput' => [
                '0' => '*REDACTED*',
                'ignoreBool' => true,
                'ignoreDouble' => 1.23,
                'ignoreInteger' => 1,
                'ignoreNull' => null,
                'ignoreString' => 'will not be masked',
                'maskBoolean' => '*REDACTED*',
                'maskDouble' => '*REDACTED*',
                'maskInteger' => '*REDACTED*',
                'maskNull' => '*REDACTED*',
                'maskString' => '*REDACTED*',
                'a sub-array' => [
                    'ignoreBool' => true,
                    'ignoreDouble' => 1.23,
                    'ignoreInteger' => 1,
                    'ignoreNull' => null,
                    'ignoreString' => 'will not be masked',
                    'maskBoolean' => '*REDACTED*',
                    'maskDouble' => '*REDACTED*',
                    'maskInteger' => '*REDACTED*',
                    'maskNull' => '*REDACTED*',
                    'maskString' => '*REDACTED*',
                ],
                'a sub-object (instance of \stdClass)' => [
                    'ignoreBool' => true,
                    'ignoreDouble' => 1.23,
                    'ignoreInteger' => 1,
                    'ignoreNull' => null,
                    'ignoreString' => 'will not be masked',
                    'maskBoolean' => '*REDACTED*',
                    'maskDouble' => '*REDACTED*',
                    'maskInteger' => '*REDACTED*',
                    'maskNull' => '*REDACTED*',
                    'maskString' => '*REDACTED*',
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
                'a sub-object (instance of \EonX\EasyUtils\Tests\SensitiveData\Fixtures\Dto\ObjectDto)' => [
                    'prop1' => '*REDACTED*',
                    'prop2' => 'some-long-name',
                    'prop3' => 'some-short-name',
                ],
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'maskboolean',
                'maskdouble',
                'maskinteger',
                'masknull',
                'maskstring',
                'prop1',
                '0',
            ],
        ];
        yield 'Mask keys in URL' => [
            'input' => [
                'mask token' => 'tcp://my-name@yeah?token=token-to-be-masked&PhoneNumber=61000000001&test=1',
                'mask token in path' => 'some text, https://example.com/path/to/token/to-be-masked?query=1',
                'mask token in path end with #' => 'some text, https://example.com/path/to/token/to-be-masked#query=1',
                'mask token in both' => 'https://example.com/path/to/token/to-be-masked?token=token-to-be-masked',
                'mask token with slash in query value (base64 can contain it)' =>
                    'https://eonx.com/cb?token=ab/cd+ef==&next=1',
                'mask token array style with slash' => 'https://eonx.com/cb?[token]=a/b/c&x=1',
                'mask token in path keeps following segment' => 'https://eonx.com/token/a1b2c3/details',
                'mask token in path at end of url in text' => 'see /token/a1b2c3 for details',
                'mask token in path in access log line' => '127.0.0.1 - "GET /token/a1b2c3 HTTP/1.1" 200 "Symfony"',
                'mask token in quoted url path' => "url 'https://eonx.com/token/a1b2c3' ok",
                'mask token whose value is a url' =>
                    'https://app.example/cb?token=https://cb.example/a/b&next=1',
                'mask token with slash before fragment' => 'https://app.example/cb?token=a/b#frag',
                'mask repeated token keys with slashes' => 'https://app.example/cb?token=a/b&token=c/d',
            ],
            'expectedOutput' => [
                'mask token' => 'tcp://my-name@yeah?token=*REDACTED*&PhoneNumber=*REDACTED*&test=1',
                'mask token in path' => 'some text, https://example.com/path/to/token/*REDACTED*?query=1',
                'mask token in path end with #' => 'some text, https://example.com/path/to/token/*REDACTED*#query=1',
                'mask token in both' => 'https://example.com/path/to/token/*REDACTED*?token=*REDACTED*',
                'mask token with slash in query value (base64 can contain it)' =>
                    'https://eonx.com/cb?token=*REDACTED*&next=1',
                'mask token array style with slash' => 'https://eonx.com/cb?[token]=*REDACTED*&x=1',
                'mask token in path keeps following segment' => 'https://eonx.com/token/*REDACTED*/details',
                'mask token in path at end of url in text' => 'see /token/*REDACTED* for details',
                'mask token in path in access log line' => '127.0.0.1 - "GET /token/*REDACTED* HTTP/1.1" 200 "Symfony"',
                'mask token in quoted url path' => "url 'https://eonx.com/token/*REDACTED*' ok",
                'mask token whose value is a url' => 'https://app.example/cb?token=*REDACTED*&next=1',
                'mask token with slash before fragment' => 'https://app.example/cb?token=*REDACTED*#frag',
                'mask repeated token keys with slashes' =>
                    'https://app.example/cb?token=*REDACTED*&token=*REDACTED*',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'token',
                'phonenumber',
            ],
        ];
        yield 'Mask keys in JSON' => [
            'input' => [
                // AuthorizationString
                'maskAuthorizationString' => 'Authorization: SOME|\/"TOKEN==12345',
                'maskAuthorizationStringInText' =>
                    'Some text before Authorization: SOME|\/"TOKEN==12345 some text after',
                'maskAuthorizationStringInMultilineText' =>
                    "Some text before\nAuthorization: SOME|\/\"TOKEN==12345\nsome text after",
                // String
                'maskString' => '{"stringValue":"will be masked","anotherStringValue":"will not be masked"}',
                'maskStringWithOneKey' => '{"stringValue":"will be masked"}',
                'maskStringWithSpaceBeforeValue' =>
                    '{"stringValue": "will be masked","anotherStringValue": "will not be masked"}',
                'maskStringWithSpaceAfterKey' =>
                    '{"stringValue" :"will be masked","anotherStringValue" :"will not be masked"}',
                'maskStringWithBothSpaces' =>
                    '{"stringValue" : "will be masked","anotherStringValue" : "will not be masked"}',
                'maskStringWithDoubleSpaces' =>
                    '{"stringValue"  :  "will be masked","anotherStringValue"  :  "will not be masked"}',
                'maskStringWithEscaping' =>
                    '{\"stringValue\":\"will be masked\",\"anotherStringValue\":\"will not be masked\"}',
                'maskStringWithOneKeyAndEscaping' => '{\"stringValue\":\"will be masked\"}',
                'maskStringWithSpaceBeforeValueAndEscaping' =>
                    '{\"stringValue\": \"will be masked\",\"anotherStringValue\": \"will not be masked\"}',
                'maskStringWithSpaceAfterKeyAndEscaping' =>
                    '{\"stringValue\" :\"will be masked\",\"anotherStringValue\" :\"will not be masked\"}',
                'maskStringWithBothSpacesAndEscaping' =>
                    '{\"stringValue\" : \"will be masked\",\"anotherStringValue\" : \"will not be masked\"}',
                'maskStringWithDoubleSpacesAndEscaping' =>
                    '{\"stringValue\"  :  \"will be masked\",\"anotherStringValue\"  :  \"will not be masked\"}',
                // Integer
                'maskInteger' => '{"integerValue":123,"anotherIntegerValue":123}',
                'maskIntegerWithOneKey' => '{"integerValue":123}',
                'maskIntegerSpaceBeforeValue' => '{"integerValue": 123,"anotherIntegerValue": 123}',
                'maskIntegerSpaceAfterKey' => '{"integerValue" :123,"anotherIntegerValue" :123}',
                'maskIntegerWithBothSpaces' => '{"integerValue" : 123,"anotherIntegerValue" : 123}',
                'maskIntegerWithDoubleSpaces' => '{"integerValue"  :  123,"anotherIntegerValue"  :  123}',
                'maskIntegerWithEscaping' => '{\"integerValue\":123,\"anotherIntegerValue\":123}',
                'maskIntegerWithOneKeyAndEscaping' => '{\"integerValue\":123}',
                'maskIntegerSpaceBeforeValueAndEscaping' => '{\"integerValue\": 123,\"anotherIntegerValue\": 123}',
                'maskIntegerSpaceAfterKeyAndEscaping' => '{\"integerValue\" :123,\"anotherIntegerValue\" :123}',
                'maskIntegerWithBothSpacesAndEscaping' => '{\"integerValue\" : 123,\"anotherIntegerValue\" : 123}',
                'maskIntegerWithDoubleSpacesAndEscaping' =>
                    '{\"integerValue\"  :  123,\"anotherIntegerValue\"  :  123}',
                // Double
                'maskDouble' => '{"doubleValue":123.45,"anotherDoubleValue":123.45}',
                'maskDoubleWithOneKey' => '{"doubleValue":123.45}',
                'maskDoubleSpaceBeforeValue' => '{"doubleValue": 123.45,"anotherDoubleValue": 123.45}',
                'maskDoubleSpaceAfterKey' => '{"doubleValue" :123.45,"anotherDoubleValue" :123.45}',
                'maskDoubleWithBothSpaces' => '{"doubleValue" : 123.45,"anotherDoubleValue" : 123.45}',
                'maskDoubleWithDoubleSpaces' => '{"doubleValue"  :  123.45,"anotherDoubleValue"  :  123.45}',
                'maskDoubleWithEscaping' => '{\"doubleValue\":123.45,\"anotherDoubleValue\":123.45}',
                'maskDoubleWithValueEscaping' => '{"someKey":"{\"doubleValue\":123.45,\"anotherDoubleValue\":123.45}"}',
                'maskDoubleWithDoubleEscaping' => '{\\"doubleValue\\":123.45,\\"anotherDoubleValue\\":123.45}',
                'maskDoubleWithOneKeyAndEscaping' => '{\"doubleValue\":123.45}',
                'maskDoubleSpaceBeforeValueAndEscaping' => '{\"doubleValue\": 123.45,\"anotherDoubleValue\": 123.45}',
                'maskDoubleSpaceAfterKeyAndEscaping' => '{\"doubleValue\" :123.45,\"anotherDoubleValue\" :123.45}',
                'maskDoubleWithBothSpacesAndEscaping' => '{\"doubleValue\" : 123.45,\"anotherDoubleValue\" : 123.45}',
                'maskDoubleWithDoubleSpacesAndEscaping' =>
                    '{\"doubleValue\"  :  123.45,\"anotherDoubleValue\"  :  123.45}',
                // Boolean
                'maskBoolean' => '{"booleanValue":true,"anotherBooleanValue":false}',
                'maskBooleanWithOneKey' => '{"booleanValue":true}',
                'maskBooleanSpaceBeforeValue' => '{"booleanValue": true,"anotherBooleanValue": false}',
                'maskBooleanSpaceAfterKey' => '{"booleanValue" :true,"anotherBooleanValue" :false}',
                'maskBooleanWithBothSpaces' => '{"booleanValue" : true,"anotherBooleanValue" : false}',
                'maskBooleanWithDoubleSpaces' => '{"booleanValue"  :  true,"anotherBooleanValue"  :  false}',
                'maskBooleanWithEscaping' => '{\"booleanValue\":true,\"anotherBooleanValue\":false}',
                'maskBooleanWithOneKeyAndEscaping' => '{\"booleanValue\":true}',
                'maskBooleanSpaceBeforeValueAndEscaping' => '{\"booleanValue\": true,\"anotherBooleanValue\": false}',
                'maskBooleanSpaceAfterKeyAndEscaping' => '{\"booleanValue\" :true,\"anotherBooleanValue\" :false}',
                'maskBooleanWithBothSpacesAndEscaping' => '{\"booleanValue\" : true,\"anotherBooleanValue\" : false}',
                'maskBooleanWithDoubleSpacesAndEscaping' =>
                    '{\"booleanValue\"  :  true,\"anotherBooleanValue\"  :  false}',
                // Null
                'maskNull' => '{"nullValue":null,"anotherNullValue":null}',
                'maskNullWithOneKey' => '{"nullValue":null}',
                'maskNullSpaceBeforeValue' => '{"nullValue": null,"anotherNullValue": null}',
                'maskNullSpaceAfterKey' => '{"nullValue" :null,"anotherNullValue" :null}',
                'maskNullWithBothSpaces' => '{"nullValue" : null,"anotherNullValue" : null}',
                'maskNullWithDoubleSpaces' => '{"nullValue"  :  null,"anotherNullValue"  :  null}',
                'maskNullWithEscaping' => '{\"nullValue\":null,\"anotherNullValue\":null}',
                'maskNullWithOneKeyAndEscaping' => '{\"nullValue\":null}',
                'maskNullSpaceBeforeValueAndEscaping' => '{\"nullValue\": null,\"anotherNullValue\": null}',
                'maskNullSpaceAfterKeyAndEscaping' => '{\"nullValue\" :null,\"anotherNullValue\" :null}',
                'maskNullWithBothSpacesAndEscaping' => '{\"nullValue\" : null,\"anotherNullValue\" : null}',
                'maskNullWithDoubleSpacesAndEscaping' => '{\"nullValue\"  :  null,\"anotherNullValue\"  :  null}',
                // Array
                'maskArray' => '{"arrayValue":["test",1,1.23,true,null],"anotherArrayValue":["test"]}',
                'maskArrayWithOneKey' => '{"arrayValue":["test",1,1.23,true,null]}',
                'maskArrayWithSpaceBeforeValue' =>
                    '{"arrayValue": ["test",1,1.23,true,null],"anotherArrayValue": ["test"]}',
                'maskArrayWithSpaceAfterKey' =>
                    '{"arrayValue" :["test",1,1.23,true,null],"anotherArrayValue" :["test"]}',
                'maskArrayWithBothSpaces' =>
                    '{"arrayValue" : ["test",1,1.23,true,null],"anotherArrayValue" : ["test"]}',
                'maskArrayWithDoubleSpaces' =>
                    '{"arrayValue"  :  ["test",1,1.23,true,null],"anotherArrayValue"  :  ["test"]}',
                'maskArrayWithEscaping' =>
                    '{\"arrayValue\":[\"test\",1,1.23,true,null],\"anotherArrayValue\":[\"test\"]}',
                'maskArrayWithOneKeyAndEscaping' =>
                    '{\"arrayValue\":[\"test\",1,1.23,true,null],\"anotherArrayValue\":[\"test\"]}',
                'maskArrayWithSpaceBeforeValueAndEscaping' =>
                    '{\"arrayValue\": [\"test\",1,1.23,true,null],\"anotherArrayValue\": [\"test\"]}',
                'maskArrayWithSpaceAfterKeyAndEscaping' =>
                    '{\"arrayValue\" :[\"test\",1,1.23,true,null],\"anotherArrayValue\" :[\"test\"]}',
                'maskArrayWithBothSpacesAndEscaping' =>
                    '{\"arrayValue\" : [\"test\",1,1.23,true,null],\"anotherArrayValue\" : [\"test\"]}',
                'maskArrayWithDoubleSpacesAndEscaping' =>
                    '{\"arrayValue\"  :  [\"test\",1,1.23,true,null],\"anotherArrayValue\"  :  [\"test\"]}',
                // Object
                'maskObject' => '{"objectValue":{"foo":"bar"},"anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithOneKey' => '{"objectValue":{"foo":"bar"}}',
                'maskObjectSpaceBeforeValue' => '{"objectValue": {"foo":"bar"},"anotherObjectValue": {"foo":"bar"}}',
                'maskObjectSpaceAfterKey' => '{"objectValue" :{"foo":"bar"},"anotherObjectValue" :{"foo":"bar"}}',
                'maskObjectWithBothSpaces' => '{"objectValue" : {"foo":"bar"},"anotherObjectValue" : {"foo":"bar"}}',
                'maskObjectWithDoubleSpaces' =>
                    '{"objectValue"  :  {"foo":"bar"},"anotherObjectValue"  :  {"foo":"bar"}}',
                'maskObjectWithEscaping' =>
                    '{\"objectValue\":{\"foo\":\"bar\"},\"anotherObjectValue\":{\"foo\":\"bar\"}}',
                'maskObjectWithOneKeyAndEscaping' => '{\"objectValue\":{\"foo\":\"bar\"}}',
                'maskObjectSpaceBeforeValueAndEscaping' =>
                    '{\"objectValue\": {\"foo\":\"bar\"},\"anotherObjectValue\": {\"foo\":\"bar\"}}',
                'maskObjectSpaceAfterKeyAndEscaping' =>
                    '{\"objectValue\" :{\"foo\":\"bar\"},\"anotherObjectValue\" :{\"foo\":\"bar\"}}',
                'maskObjectWithBothSpacesAndEscaping' =>
                    '{\"objectValue\" : {\"foo\":\"bar\"},\"anotherObjectValue\" : {\"foo\":\"bar\"}}',
                'maskObjectWithDoubleSpacesAndEscaping' =>
                    '{\"objectValue\"  :  {\"foo\":\"bar\"},\"anotherObjectValue\"  :  {\"foo\":\"bar\"}}',
                // Nested
                'maskNested' =>
                    '{"test":{"stringValue":"will be masked","integerValue":123,"doubleValue":123.45,' .
                    '"booleanValue":true,"nullValue":null,"arrayValue":["test",1,1.23,true,null],' .
                    '"objectValue":{"foo":"bar"}}}',
                'multipleNested' =>
                    '{"test":{"arrayValue":[[1,2,3],[4,5,6]],"objectValue":{"foo":{"baz":"bar","foo":{"baz":"bar"}}}}}',
                'maskNestedWithEscaping' =>
                    '{\"test\":{\"stringValue\":\"will be masked\",\"integerValue\":123,\"doubleValue\":123.45,' .
                    '\"booleanValue\":true,\"nullValue\":null,\"arrayValue\":[\"test\",1,1.23,true,null],' .
                    '\"objectValue\":{\"foo\":\"bar\"}}}',
            ],
            'expectedOutput' => [
                // AuthorizationString
                'maskAuthorizationString' => 'Authorization: *REDACTED*',
                'maskAuthorizationStringInText' => 'Some text before Authorization: *REDACTED*',
                'maskAuthorizationStringInMultilineText' =>
                    "Some text before\nAuthorization: *REDACTED*\nsome text after",
                // String
                'maskString' => '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithOneKey' => '{"stringValue":"*REDACTED*"}',
                'maskStringWithSpaceBeforeValue' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithSpaceAfterKey' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithBothSpaces' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithDoubleSpaces' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithEscaping' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithOneKeyAndEscaping' => '{"stringValue":"*REDACTED*"}',
                'maskStringWithSpaceBeforeValueAndEscaping' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithSpaceAfterKeyAndEscaping' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithBothSpacesAndEscaping' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithDoubleSpacesAndEscaping' =>
                    '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                // Integer
                'maskInteger' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithOneKey' => '{"integerValue":"*REDACTED*"}',
                'maskIntegerSpaceBeforeValue' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerSpaceAfterKey' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithBothSpaces' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithDoubleSpaces' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithEscaping' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithOneKeyAndEscaping' => '{"integerValue":"*REDACTED*"}',
                'maskIntegerSpaceBeforeValueAndEscaping' =>
                    '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerSpaceAfterKeyAndEscaping' =>
                    '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithBothSpacesAndEscaping' =>
                    '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithDoubleSpacesAndEscaping' =>
                    '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                // Double
                'maskDouble' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithOneKey' => '{"doubleValue":"*REDACTED*"}',
                'maskDoubleSpaceBeforeValue' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleSpaceAfterKey' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithBothSpaces' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithDoubleSpaces' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithEscaping' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithValueEscaping' =>
                    '{"someKey":"{\"doubleValue\":\"*REDACTED*\",\"anotherDoubleValue\":123.45}"}',
                'maskDoubleWithDoubleEscaping' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithOneKeyAndEscaping' => '{"doubleValue":"*REDACTED*"}',
                'maskDoubleSpaceBeforeValueAndEscaping' =>
                    '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleSpaceAfterKeyAndEscaping' =>
                    '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithBothSpacesAndEscaping' =>
                    '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithDoubleSpacesAndEscaping' =>
                    '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                // Boolean
                'maskBoolean' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithOneKey' => '{"booleanValue":"*REDACTED*"}',
                'maskBooleanSpaceBeforeValue' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanSpaceAfterKey' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithBothSpaces' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithDoubleSpaces' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithEscaping' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithOneKeyAndEscaping' => '{"booleanValue":"*REDACTED*"}',
                'maskBooleanSpaceBeforeValueAndEscaping' =>
                    '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanSpaceAfterKeyAndEscaping' =>
                    '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithBothSpacesAndEscaping' =>
                    '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithDoubleSpacesAndEscaping' =>
                    '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                // Null
                'maskNull' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithOneKey' => '{"nullValue":"*REDACTED*"}',
                'maskNullSpaceBeforeValue' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullSpaceAfterKey' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithBothSpaces' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithDoubleSpaces' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithEscaping' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithOneKeyAndEscaping' => '{"nullValue":"*REDACTED*"}',
                'maskNullSpaceBeforeValueAndEscaping' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullSpaceAfterKeyAndEscaping' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithBothSpacesAndEscaping' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithDoubleSpacesAndEscaping' =>
                    '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                // Array
                'maskArray' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithOneKey' => '{"arrayValue":"*REDACTED*"}',
                'maskArrayWithSpaceBeforeValue' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithSpaceAfterKey' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithBothSpaces' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithDoubleSpaces' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithEscaping' => '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithOneKeyAndEscaping' =>
                    '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithSpaceBeforeValueAndEscaping' =>
                    '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithSpaceAfterKeyAndEscaping' =>
                    '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithBothSpacesAndEscaping' =>
                    '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                'maskArrayWithDoubleSpacesAndEscaping' =>
                    '{"arrayValue":"*REDACTED*","anotherArrayValue":["test"]}',
                // Object
                'maskObject' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithOneKey' => '{"objectValue":"*REDACTED*"}',
                'maskObjectSpaceBeforeValue' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectSpaceAfterKey' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithBothSpaces' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithDoubleSpaces' =>
                    '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithEscaping' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithOneKeyAndEscaping' => '{"objectValue":"*REDACTED*"}',
                'maskObjectSpaceBeforeValueAndEscaping' =>
                    '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectSpaceAfterKeyAndEscaping' =>
                    '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithBothSpacesAndEscaping' =>
                    '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithDoubleSpacesAndEscaping' =>
                    '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                // Nested
                'maskNested' =>
                    '{"test":{"stringValue":"*REDACTED*","integerValue":"*REDACTED*","doubleValue":"*REDACTED*",' .
                    '"booleanValue":"*REDACTED*","nullValue":"*REDACTED*","arrayValue":"*REDACTED*",' .
                    '"objectValue":"*REDACTED*"}}',
                'multipleNested' => '{"test":{"arrayValue":"*REDACTED*","objectValue":"*REDACTED*"}}',
                'maskNestedWithEscaping' =>
                    '{"test":{"stringValue":"*REDACTED*","integerValue":"*REDACTED*",' .
                    '"doubleValue":"*REDACTED*","booleanValue":"*REDACTED*",' .
                    '"nullValue":"*REDACTED*","arrayValue":"*REDACTED*","objectValue":"*REDACTED*"}}',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'arrayvalue',
                'booleanvalue',
                'doublevalue',
                'integervalue',
                'nullvalue',
                'objectvalue',
                'stringvalue',
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
                'cardAfterNonCardDigits' => 'order 1234567890123456 paid 4242 4242 4242 4242',
                'cardAfterLongDigits' => 'ref 20260702030753654038 pan 4005 5500 0000 0001',
                'twoCardsMaskedIndependently' => 'card 4005 5500 0000 0001 or 5313 5810 0012 3430',
                'cardAfterNonCardInUrl' =>
                    'https://eonx.com/page?ref=1234567890123456&card=4005 5500 0000 0001',
                'thirteenDigitVisa' => '4222222222222',
                'withDashes' => '4005-5500-0000-0001',
                'trailingPunctuationKept' => 'pan 4005 5500 0000 0001.',
                'twoCardsCommaSeparated' => '4005 5500 0000 0001, 5313 5810 0012 3430',
                'cardAmongCommaSeparatedValues' => '(131, 4005550000000001, 20260702, AUD)',
                'cardAfterAmountAndComma' => 'ref 12345678, 4005 5500 0000 0001',
                'cardsSeparatedByNewline' => "4005 5500 0000 0001\n5313 5810 0012 3430",
                'cardsSeparatedByTab' => "4005550000000001\t5313581000123430",
                'cardsSeparatedBySemicolon' => '4005550000000001;5313581000123430',
                'cardInCommaListNoSpaces' => '(131,4005550000000001,20260702,AUD)',
                'cardAfterSlashSeparatedDigits' => '123/4005550000000001',
                'cardAfterPlusSeparatedDigits' => '123+4005550000000001',
                'uuidV4' => 'edf57c2c-5758-4694-9868-24e2430321e7',
                'uuidV6' => '1f1dc398-654c-6538-9652-0579065566b8',
                'uuidV6CandidateFromSecondGroup' => '1f1dc3b6-8240-6162-8258-c541362575db',
                'uuidV7' => '01a1c057-5ed2-7592-8df5-76889961156d',
                'uuidUppercase' => '1F1DC398-654C-6538-9652-0579065566B8',
                'uuidInSentence' => 'job 1f1dc398-654c-6538-9652-0579065566b8 failed',
                'uuidInJson' => '{"id":"1f1dc398-654c-6538-9652-0579065566b8"}',
                'uuidInUrl' => 'https://eonx.com/jobs/1f1dc398-654c-6538-9652-0579065566b8?x=1',
                'cardAfterUuid' => '1f1dc398-654c-6538-9652-0579065566b8 4005 5500 0000 0001',
                'cardBeforeUuid' => '4005550000000001 1f1dc398-654c-6538-9652-0579065566b8',
                'cardDigitsInsideNonCardNumberKept' => 'card 4005550000000001 ref 94005550000000001',
                'cardFollowedByNumber' => '4005 5500 0000 0001 1226',
                'cardAfterNumber' => '1225 4005 5500 0000 0001',
                'cardFollowedByDashedNumber' => 'PAN 4005550000000001-100',
                'twoCardsSeparatedBySpace' => '4005550000000001 5313581000123430',
                'cardsAmongSpaceSeparatedNumbers' => '123 4005550000000001 456 5313 5810 0012 3430 7',
                'cardInTwoDigitGroups' => '40 05 55 00 00 00 00 01',
                'amexFollowedByNumber' => '3782 822463 10005 12',
                'cardFollowedByNumberThatMakesAnotherCard' => '4005 5500 0000 0001 1225',
            ],
            'expectedOutput' => [
                'withSpace' => '512345*REDACTED*2346',
                'withDoubleSpace' => '512345*REDACTED*2346',
                'noSpace' => '512345*REDACTED*2346',
                'withDots' => '512345*REDACTED*2346',
                'withSlashes' => '512345*REDACTED*2346',
                'withinSentenceWithSpace' => 'fewfewkjfewljl 512345*REDACTED*2346 few wfewgew ',
                'withinSentenceNoSpace' => 'fewfewkjfewljl512345*REDACTED*2346fewwfewgew',
                'Mastercard' => '531358*REDACTED*3430',
                'Visa' => '400555*REDACTED*0001',
                'Amex' => '345678*REDACTED*4564',
                'inJson' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithEscaping' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithSpaceBeforeValue' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithSpaceAfterKey' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithBothSpaces' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithDoubleSpaces' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithSpaceBeforeValueAndEscaping' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithSpaceAfterKeyAndEscaping' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithBothSpacesAndEscaping' => '{"card":"400555*REDACTED*0001"}',
                'inJsonWithDoubleSpacesAndEscaping' => '{"card":"400555*REDACTED*0001"}',
                'inUrl' => 'https://eonx.com/page?card=400555*REDACTED*0001',
                'nonCardNumber' => '1234567890123456',
                'cardAfterNonCardDigits' => 'order 1234567890123456 paid 424242*REDACTED*4242',
                'cardAfterLongDigits' => 'ref 20260702030753654038 pan 400555*REDACTED*0001',
                'twoCardsMaskedIndependently' => 'card 400555*REDACTED*0001 or 531358*REDACTED*3430',
                'cardAfterNonCardInUrl' =>
                    'https://eonx.com/page?ref=1234567890123456&card=400555*REDACTED*0001',
                'thirteenDigitVisa' => '422222*REDACTED*2222',
                'withDashes' => '400555*REDACTED*0001',
                'trailingPunctuationKept' => 'pan 400555*REDACTED*0001.',
                'twoCardsCommaSeparated' => '400555*REDACTED*0001, 531358*REDACTED*3430',
                'cardAmongCommaSeparatedValues' => '(131, 400555*REDACTED*0001, 20260702, AUD)',
                'cardAfterAmountAndComma' => 'ref 12345678, 400555*REDACTED*0001',
                'cardsSeparatedByNewline' => "400555*REDACTED*0001\n531358*REDACTED*3430",
                'cardsSeparatedByTab' => "400555*REDACTED*0001\t531358*REDACTED*3430",
                'cardsSeparatedBySemicolon' => '400555*REDACTED*0001;531358*REDACTED*3430',
                'cardInCommaListNoSpaces' => '(131,400555*REDACTED*0001,20260702,AUD)',
                'cardAfterSlashSeparatedDigits' => '123/400555*REDACTED*0001',
                'cardAfterPlusSeparatedDigits' => '123+400555*REDACTED*0001',
                'uuidV4' => 'edf57c2c-5758-4694-9868-24e2430321e7',
                'uuidV6' => '1f1dc398-654c-6538-9652-0579065566b8',
                'uuidV6CandidateFromSecondGroup' => '1f1dc3b6-8240-6162-8258-c541362575db',
                'uuidV7' => '01a1c057-5ed2-7592-8df5-76889961156d',
                'uuidUppercase' => '1F1DC398-654C-6538-9652-0579065566B8',
                'uuidInSentence' => 'job 1f1dc398-654c-6538-9652-0579065566b8 failed',
                'uuidInJson' => '{"id":"1f1dc398-654c-6538-9652-0579065566b8"}',
                'uuidInUrl' => 'https://eonx.com/jobs/1f1dc398-654c-6538-9652-0579065566b8?x=1',
                'cardAfterUuid' => '1f1dc398-654c-6538-9652-0579065566b8 400555*REDACTED*0001',
                'cardBeforeUuid' => '400555*REDACTED*0001 1f1dc398-654c-6538-9652-0579065566b8',
                'cardDigitsInsideNonCardNumberKept' => 'card 400555*REDACTED*0001 ref 94005550000000001',
                'cardFollowedByNumber' => '400555*REDACTED*0001 1226',
                'cardAfterNumber' => '1225 400555*REDACTED*0001',
                'cardFollowedByDashedNumber' => 'PAN 400555*REDACTED*0001-100',
                'twoCardsSeparatedBySpace' => '400555*REDACTED*0001 531358*REDACTED*3430',
                'cardsAmongSpaceSeparatedNumbers' => '123 400555*REDACTED*0001 456 531358*REDACTED*3430 7',
                'cardInTwoDigitGroups' => '400555*REDACTED*0001',
                'amexFollowedByNumber' => '378282*REDACTED*0005 12',
                'cardFollowedByNumberThatMakesAnotherCard' => '400555*REDACTED*1225',
            ],
            'maskPattern' => '*REDACTED*',
        ];
        yield 'Keep number lists that are not card numbers' => [
            'input' => [
                'ips' => '94.199.243.92 241.190.46.160',
                'moreIps' => '248.157.37.98 144.187.20.113 110.57.21.121',
                'sixDigitIds' => '157898 646390 264448 658889',
                'moreSixDigitIds' => '655082 414563 920656 602041',
                'amounts' => '15567.67 70061.85 50303.53 58464.67 25250.02',
                'dates' => '2003-09-06 2005-10-02 2028-03-24 2003-07-28 2008-12-11',
                'cardInTwoDigitGroupsFollowedByNumber' => '40 05 55 00 00 00 00 01 1226',
                'fourDigitNumberThenThreeDigitIds' => '4467 000 065 232 432',
                'fiveDigitNumberThenFourDigitIds' => '41592 1857 6031 10',
                'threeDigitNumberThenFourDigitIds' => '378 2822 4631 0005 7',
            ],
            'expectedOutput' => [
                'ips' => '94.199.243.92 241.190.46.160',
                'moreIps' => '248.157.37.98 144.187.20.113 110.57.21.121',
                'sixDigitIds' => '157898 646390 264448 658889',
                'moreSixDigitIds' => '655082 414563 920656 602041',
                'amounts' => '15567.67 70061.85 50303.53 58464.67 25250.02',
                'dates' => '2003-09-06 2005-10-02 2028-03-24 2003-07-28 2008-12-11',
                'cardInTwoDigitGroupsFollowedByNumber' => '40 05 55 00 00 00 00 01 1226',
                'fourDigitNumberThenThreeDigitIds' => '4467 000 065 232 432',
                'fiveDigitNumberThenFourDigitIds' => '41592 1857 6031 10',
                'threeDigitNumberThenFourDigitIds' => '378 2822 4631 0005 7',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [],
        ];
        yield 'Mask keys embedded in free-text JSON' => [
            'input' => [
                'jsonInText' => 'login failed {"password":"secret","note":"ok"}',
                'jsonPrefixed' => 'payload={"access_token":"xyz","id":7}',
                'jsonWithTrailingText' => '{"password":"secret"} <- received',
                'jsonBraceInStringValue' => 'log {"note":"end} x","password":"y"} tail',
                'jsonMultipleObjects' => 'a {"password":"x"} b {"access_token":"y"} c',
                'jsonArrayOfObjects' => 'items: [{"password":"a"},{"password":"b"}]',
                'jsonDeeplyNested' => 'x {"a":{"b":{"password":"p"}}} y',
                'jsonEscapedQuotesInValue' => 'v {"note":"say \"hi\"","password":"z"} w',
                'jsonInsideJsonWholeString' => '{"payload":"{\"password\":\"secret\"}","id":1}',
                'jsonInsideJsonInText' => 'log: {"payload":"{\"password\":\"secret\"}"} end',
            ],
            'expectedOutput' => [
                'jsonInText' => 'login failed {"password":"*REDACTED*","note":"ok"}',
                'jsonPrefixed' => 'payload={"access_token":"*REDACTED*","id":7}',
                'jsonWithTrailingText' => '{"password":"*REDACTED*"} <- received',
                'jsonBraceInStringValue' => 'log {"note":"end} x","password":"*REDACTED*"} tail',
                'jsonMultipleObjects' => 'a {"password":"*REDACTED*"} b {"access_token":"*REDACTED*"} c',
                'jsonArrayOfObjects' => 'items: [{"password":"*REDACTED*"},{"password":"*REDACTED*"}]',
                'jsonDeeplyNested' => 'x {"a":{"b":{"password":"*REDACTED*"}}} y',
                'jsonEscapedQuotesInValue' => 'v {"note":"say \"hi\"","password":"*REDACTED*"} w',
                'jsonInsideJsonWholeString' => '{"payload":"{\"password\":\"*REDACTED*\"}","id":1}',
                'jsonInsideJsonInText' => 'log: {"payload":"{\"password\":\"*REDACTED*\"}"} end',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'password',
                'access_token',
            ],
        ];
        yield 'Re-encoded fragment keeps slashes and unicode unescaped' => [
            'input' => [
                'embedded' => 'log {"note":"привет","url":"https://a/b","password":"secret"} end',
                'wholeString' => '{"note":"café","url":"https://a/b","password":"secret"}',
            ],
            'expectedOutput' => [
                'embedded' => 'log {"note":"привет","url":"https://a/b","password":"*REDACTED*"} end',
                'wholeString' => '{"note":"café","url":"https://a/b","password":"*REDACTED*"}',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'password',
            ],
        ];
        yield 'Mask key=value in free text' => [
            'input' => [
                'detailList' => 'DETAIL: (password=secret, token=abc)',
                'inSentence' => 'user password=hunter2 for id=7',
                'semicolonSeparated' => 'password=secret;token=abc',
                'ampSeparated' => 'password=secret&token=abc',
            ],
            'expectedOutput' => [
                'detailList' => 'DETAIL: (password=*REDACTED*, token=*REDACTED*)',
                'inSentence' => 'user password=*REDACTED* for id=7',
                'semicolonSeparated' => 'password=*REDACTED*;token=*REDACTED*',
                'ampSeparated' => 'password=*REDACTED*&token=*REDACTED*',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'password',
                'token',
            ],
        ];
        yield 'Mask pattern with regex-replacement metacharacters is literal' => [
            'input' => [
                'card' => '4005 5500 0000 0001',
                'url' => 'https://example.com?token=secret',
                'authHeader' => 'Authorization: Bearer xyz',
            ],
            'expectedOutput' => [
                'card' => '400555$redacted$1$0001',
                'url' => 'https://example.com?token=$redacted$1$',
                'authHeader' => 'Authorization: $redacted$1$',
            ],
            'maskPattern' => '$redacted$1$',
            'keysToMask' => [
                'token',
            ],
        ];
        yield 'Mask keys containing regex-special characters' => [
            'input' => [
                'dotKey' => 'a.b=secret axb=keep',
                'slashKey' => 'a/b=secret&c=keep',
                'plusKey' => 'a+b=secret;d=keep',
                'plusAfterSeparatorKey' => 'api_+key=secret&keep=1',
                'plusAfterSeparatorRunKey' => 'api__+key=secret&keep=1',
                'encodedSeparatorAfterSeparatorKey' => 'api_%5Fkey=secret&keep=1',
            ],
            'expectedOutput' => [
                'dotKey' => 'a.b=*REDACTED* axb=keep',
                'slashKey' => 'a/b=*REDACTED*&c=keep',
                'plusKey' => 'a+b=*REDACTED*;d=keep',
                'plusAfterSeparatorKey' => 'api_+key=*REDACTED*&keep=1',
                'plusAfterSeparatorRunKey' => 'api__+key=*REDACTED*&keep=1',
                'encodedSeparatorAfterSeparatorKey' => 'api_%5Fkey=*REDACTED*&keep=1',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'a.b',
                'a/b',
                'a+b',
                'api_+key',
                'api_%5Fkey',
            ],
        ];
        yield 'Mask pattern starting with a digit' => [
            'input' => [
                'card' => '4005 5500 0000 0001',
                'url' => 'https://example.com?token=secret',
                'authHeader' => 'Authorization: Bearer xyz',
            ],
            'expectedOutput' => [
                'card' => '4005551X0001',
                'url' => 'https://example.com?token=1X',
                'authHeader' => 'Authorization: 1X',
            ],
            'maskPattern' => '1X',
            'keysToMask' => [
                'token',
            ],
        ];
        yield 'Mask keys regardless of separators, case and "x" prefix' => [
            'input' => [
                'api key' => 'secret',
                'api-key' => 'secret',
                'api_key' => 'secret',
                'apikey' => 'secret',
                'apiKey' => 'secret',
                'API_KEY' => 'secret',
                'x api key' => 'secret',
                'x-api-key' => 'secret',
                'X-Api-Key' => 'secret',
                'x_api_key' => 'secret',
                'xapi_key' => 'secret',
                'xapikey' => 'secret',
                'x-auth-token' => 'secret',
                'headers' => [
                    'x-access-token' => ['secret'],
                ],
                'json' => '{"accessToken":"secret","tokenType":"Bearer"}',
                'api_key_id' => 'keep',
                'my_api_key' => 'keep',
                'token_type' => 'keep',
                'next_page_token' => 'keep',
                'x' => 'keep',
            ],
            'expectedOutput' => [
                'api key' => '*REDACTED*',
                'api-key' => '*REDACTED*',
                'api_key' => '*REDACTED*',
                'apikey' => '*REDACTED*',
                'apiKey' => '*REDACTED*',
                'API_KEY' => '*REDACTED*',
                'x api key' => '*REDACTED*',
                'x-api-key' => '*REDACTED*',
                'X-Api-Key' => '*REDACTED*',
                'x_api_key' => '*REDACTED*',
                'xapi_key' => '*REDACTED*',
                'xapikey' => '*REDACTED*',
                'x-auth-token' => '*REDACTED*',
                'headers' => [
                    'x-access-token' => '*REDACTED*',
                ],
                'json' => '{"accessToken":"*REDACTED*","tokenType":"Bearer"}',
                'api_key_id' => 'keep',
                'my_api_key' => 'keep',
                'token_type' => 'keep',
                'next_page_token' => 'keep',
                'x' => 'keep',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'access_token',
                'api key',
                'Auth-Token',
            ],
        ];
        yield 'Mask key=value in free text regardless of separators' => [
            'input' => [
                'query' => 'https://example.com?api-key=secret1&apiKey=secret2&api_key=secret3&X-Api-Key=secret4',
                'path' => 'https://example.com/api-key/secret5/keep',
                'otherKey' => 'apikeys=keep',
            ],
            'expectedOutput' => [
                'query' => 'https://example.com?api-key=*REDACTED*&apiKey=*REDACTED*&api_key=*REDACTED*'
                    . '&X-Api-Key=*REDACTED*',
                'path' => 'https://example.com/api-key/*REDACTED*/keep',
                'otherKey' => 'apikeys=keep',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'api key',
            ],
        ];
        yield 'Mask key=value in free text with repeated, edge separators and "x" prefix' => [
            'input' => [
                'repeated' => 'https://example.com/api__key/secret1/keep?api__key=secret2',
                'edge' => 'https://example.com?_token_=secret3',
                'prefixed' => 'https://example.com/x-shared-key/secret4/keep?a[x_shared_key]=secret5',
            ],
            'expectedOutput' => [
                'repeated' => 'https://example.com/api__key/*REDACTED*/keep?api__key=*REDACTED*',
                'edge' => 'https://example.com?_token_=*REDACTED*',
                'prefixed' => 'https://example.com/x-shared-key/*REDACTED*/keep?a[x_shared_key]=*REDACTED*',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'api__key',
                '_token_',
                'shared_key',
            ],
        ];
        yield 'Mask default keys' => [
            'input' => [
                'cookie' => 'some-value',
                'name' => 'some-value',
                'proxy-authorization' => 'some-value',
                'refresh_token' => 'some-value',
                'refreshToken' => 'some-value',
                'secret' => 'some-value',
                'secret_key' => 'some-value',
                'secretKey' => 'some-value',
                'set-cookie' => 'some-value',
                'x-webhook-signature' => 'some-value',
                'auth_ntlm' => 'some-value',
                'passphrase' => 'some-value',
                'X-Amz-Security-Token' => 'some-value',
            ],
            'expectedOutput' => [
                'cookie' => '*REDACTED*',
                'name' => 'some-value',
                'proxy-authorization' => '*REDACTED*',
                'refresh_token' => '*REDACTED*',
                'refreshToken' => '*REDACTED*',
                'secret' => '*REDACTED*',
                'secret_key' => '*REDACTED*',
                'secretKey' => '*REDACTED*',
                'set-cookie' => '*REDACTED*',
                'x-webhook-signature' => '*REDACTED*',
                'auth_ntlm' => '*REDACTED*',
                'passphrase' => '*REDACTED*',
                'X-Amz-Security-Token' => '*REDACTED*',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => EasyUtilsBundle::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK,
        ];
        yield 'Mask legacy spellings of default keys in arrays and JSON' => [
            'input' => [
                'apikey' => 'secret',
                'php-auth-pw' => 'secret',
                'php-auth-user' => 'secret',
                'securitycode' => 'secret',
                'verificationcode' => 'secret',
                'x-shared-key' => 'secret',
                'json' => '{"apikey":"secret","x-shared-key":"secret","securitycode":"secret"}',
            ],
            'expectedOutput' => [
                'apikey' => '*REDACTED*',
                'php-auth-pw' => '*REDACTED*',
                'php-auth-user' => '*REDACTED*',
                'securitycode' => '*REDACTED*',
                'verificationcode' => '*REDACTED*',
                'x-shared-key' => '*REDACTED*',
                'json' => '{"apikey":"*REDACTED*","x-shared-key":"*REDACTED*","securitycode":"*REDACTED*"}',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => EasyUtilsBundle::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK,
        ];
        yield 'Mask legacy spellings of default keys in URL' => [
            'input' => [
                'path' => 'https://example.com/x-shared-key/secret1/php-auth-pw/secret2/keep',
                'query' => 'https://example.com?apikey=secret3&securitycode=secret4&verificationcode=secret5',
                'array' => 'a[php-auth-user]=secret6&a[x-shared-key]=secret7',
            ],
            'expectedOutput' => [
                'path' => 'https://example.com/x-shared-key/*REDACTED*/php-auth-pw/*REDACTED*/keep',
                'query' => 'https://example.com?apikey=*REDACTED*&securitycode=*REDACTED*'
                    . '&verificationcode=*REDACTED*',
                'array' => 'a[php-auth-user]=*REDACTED*&a[x-shared-key]=*REDACTED*',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => EasyUtilsBundle::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK,
        ];
        yield 'Mask password in URL user info' => [
            'input' => [
                'url' => 'https://user:p4ss@example.com/path?x=1',
                'proxyInText' => 'proxy http://proxy-user:s3cret@10.0.0.1:3128 failed',
                'dsn' => 'redis://:s3cret@cache:6379/0',
                'userWithoutPassword' => 'tcp://my-name@yeah',
                'email' => 'contact a@b.co: done',
                'mailto' => 'mailto:a@b.co',
                'apostropheInUser' => "https://o'connor:secret@example.com/",
                'apostropheInPassword' => "https://user:p'ss@example.com/",
            ],
            'expectedOutput' => [
                'url' => 'https://user:*REDACTED*@example.com/path?x=1',
                'proxyInText' => 'proxy http://proxy-user:*REDACTED*@10.0.0.1:3128 failed',
                'dsn' => 'redis://:*REDACTED*@cache:6379/0',
                'userWithoutPassword' => 'tcp://my-name@yeah',
                'email' => 'contact a@b.co: done',
                'mailto' => 'mailto:a@b.co',
                'apostropheInUser' => "https://o'connor:*REDACTED*@example.com/",
                'apostropheInPassword' => 'https://user:*REDACTED*@example.com/',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'token',
            ],
        ];
        yield 'Mask URL-encoded keys' => [
            'input' => [
                'encodedBrackets' => 'user%5Bpassword%5D=secret&user%5Bname%5D=keep',
                'lowercaseEncodedBrackets' => 'user%5bpassword%5d=secret&x=1',
                'encodedSeparator' => 'https://example.com?api%5Fkey=secret&x=1',
                'formEncodedSpace' => 'api+key=secret&x=1',
            ],
            'expectedOutput' => [
                'encodedBrackets' => 'user%5Bpassword%5D=*REDACTED*&user%5Bname%5D=keep',
                'lowercaseEncodedBrackets' => 'user%5bpassword%5d=*REDACTED*&x=1',
                'encodedSeparator' => 'https://example.com?api%5Fkey=*REDACTED*&x=1',
                'formEncodedSpace' => 'api+key=*REDACTED*&x=1',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'api_key',
                'password',
            ],
        ];
        yield 'Mask header lines' => [
            'input' => [
                'apiKeyHeader' => 'X-Api-Key: secret',
                'cookieHeader' => 'Cookie: a=b; c=d',
                'noSpace' => 'x-api-key:secret',
                'otherHeader' => 'Accept: application/json',
                'multiline' => "Accept: */*\nX-Api-Key: secret\r\nHost: example.com",
                'notAtLineStart' => 'note api_key: secret',
            ],
            'expectedOutput' => [
                'apiKeyHeader' => 'X-Api-Key: *REDACTED*',
                'cookieHeader' => 'Cookie: *REDACTED*',
                'noSpace' => 'x-api-key: *REDACTED*',
                'otherHeader' => 'Accept: application/json',
                'multiline' => "Accept: */*\nX-Api-Key: *REDACTED*\r\nHost: example.com",
                'notAtLineStart' => 'note api_key: secret',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'api_key',
                'cookie',
            ],
        ];
        yield 'Mask separator-only keys exactly' => [
            'input' => [
                '_' => 'secret',
                'json' => '{"_":"secret","-":"keep","__":"keep"}',
                '-' => 'keep',
                '__' => 'keep',
                'x_' => 'keep',
            ],
            'expectedOutput' => [
                '_' => '*REDACTED*',
                'json' => '{"_":"*REDACTED*","-":"keep","__":"keep"}',
                '-' => 'keep',
                '__' => 'keep',
                'x_' => 'keep',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                '_',
            ],
        ];
        yield 'Leave unbalanced or truncated JSON untouched' => [
            'input' => [
                'manyOpeners' => 'weird {{{{{{{{ end',
                'truncatedNested' => 'ctx {"a":{"b": cut off',
                'validThenTruncated' => '{"password":"secret"} then {"leftover": cut',
            ],
            'expectedOutput' => [
                'manyOpeners' => 'weird {{{{{{{{ end',
                'truncatedNested' => 'ctx {"a":{"b": cut off',
                'validThenTruncated' => '{"password":"*REDACTED*"} then {"leftover": cut',
            ],
            'maskPattern' => '*REDACTED*',
            'keysToMask' => [
                'password',
            ],
        ];
    }

    /**
     * @see testSanitizeException
     */
    public static function provideExceptionDataForSanitizing(): iterable
    {
        yield 'Mask value if key explicitly provided' => [
            'keysToMask' => ['message'],
            'maskPattern' => '*REDACTED*',
            'message' => 'This is a test message',
            'expectedMessage' => '*REDACTED*',
        ];
        yield 'Mask key in JSON' => [
            'keysToMask' => ['keyToMask'],
            'maskPattern' => '*REDACTED*',
            'message' => '{"keyToMask":"This is a test message"}',
            'expectedMessage' => '{"keyToMask":"*REDACTED*"}',
        ];
        yield 'Mask card number' => [
            'keysToMask' => ['card'],
            'maskPattern' => '*REDACTED*',
            'message' => 'This is a test message with card number 4242 4242 4242 4242',
            'expectedMessage' => 'This is a test message with card number 424242*REDACTED*4242',
        ];
    }

    public function testDoNotSanitizeCardLikeValuesThatAreNotLuhnValid(): void
    {
        $sanitizer = $this->getSanitizer('');

        $result = $sanitizer->sanitize(['uuid' => '{"uuid":"ba7e6152-1756-4391-bbe6-3bbc5057eb3d"}']);

        self::assertEquals(['uuid' => '{"uuid":"ba7e6152-1756-4391-bbe6-3bbc5057eb3d"}'], $result);
    }

    /**
     * @param string[]|null $keysToMask
     */
    #[DataProvider('provideAbstractDataForSanitizing')]
    public function testSanitize(
        array $input,
        array $expectedOutput,
        string $maskPattern,
        ?array $keysToMask = null,
    ): void {
        $sanitizer = $this->getSanitizer($maskPattern, $keysToMask);

        self::assertEquals($expectedOutput, $sanitizer->sanitize($input));
    }

    /**
     * @param string[] $keysToMask
     */
    #[DataProvider('provideExceptionDataForSanitizing')]
    public function testSanitizeException(
        array $keysToMask,
        string $maskPattern,
        string $message,
        string $expectedMessage,
    ): void {
        $sanitizer = $this->getSanitizer($maskPattern, $keysToMask);
        $exception = new RuntimeException($message);

        /** @var array $sanitizedException */
        $sanitizedException = $sanitizer->sanitize($exception);

        self::assertSame($expectedMessage, $sanitizedException['message']);
    }

    public function testSanitizeNumericCardNumbers(): void
    {
        $sanitizer = $this->getSanitizer('*REDACTED*');

        $result = $sanitizer->sanitize([
            'int' => 4111111111111111,
            'negativeInt' => -4111111111111111,
            'float' => 4111111111111111.0,
            'exponentFloat' => 4.111111111111111e15,
            'nonCardInt' => 1234567890123456,
            'shortInt' => 100000000000,
            'decimal' => 1.5,
            'nested' => [
                'note' => 4111111111111111,
            ],
            'json' => '{"note":4111111111111111}',
            'jsonInJsonString' => '{"payload":"{\\"note\\":4111111111111111}"}',
            'jsonInFreeText' => 'note {"card":4111111111111111}',
        ]);

        self::assertSame(
            [
                'int' => '411111*REDACTED*1111',
                'negativeInt' => '-411111*REDACTED*1111',
                'float' => '411111*REDACTED*1111',
                'exponentFloat' => '411111*REDACTED*1111',
                'nonCardInt' => 1234567890123456,
                'shortInt' => 100000000000,
                'decimal' => 1.5,
                'nested' => [
                    'note' => '411111*REDACTED*1111',
                ],
                'json' => '{"note":"411111*REDACTED*1111"}',
                'jsonInJsonString' => '{"payload":"{\\"note\\":\\"411111*REDACTED*1111\\"}"}',
                'jsonInFreeText' => 'note {"card":"411111*REDACTED*1111"}',
            ],
            $result
        );
        self::assertSame('411111*REDACTED*1111', $sanitizer->sanitize(4111111111111111));
        self::assertSame(1234567890123456, $sanitizer->sanitize(1234567890123456));
    }

    abstract protected function getSanitizer(
        string $maskPattern,
        ?array $keysToMask = null,
    ): SensitiveDataSanitizerInterface;
}
