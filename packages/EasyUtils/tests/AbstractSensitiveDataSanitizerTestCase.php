<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\Tests\SensitiveData\Fixtures\Dto\ObjectDto;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

abstract class AbstractSensitiveDataSanitizerTestCase extends AbstractTestCase
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
                    new ObjectDto(
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
            'maskKeys' => [
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
                'maskNestedWithEscaping' =>
                    '{\"test\":{\"stringValue\":\"will be masked\",\"integerValue\":123,\"doubleValue\":123.45,' .
                    '\"booleanValue\":true,\"nullValue\":null,\"arrayValue\":[\"test\",1,1.23,true,null],' .
                    '\"objectValue\":{\"foo\":\"bar\"}}}',
            ],
            'expectedOutput' => [
                // String
                'maskString' => '{"stringValue":"*REDACTED*","anotherStringValue":"will not be masked"}',
                'maskStringWithOneKey' => '{"stringValue":"*REDACTED*"}',
                'maskStringWithSpaceBeforeValue' =>
                    '{"stringValue": "*REDACTED*","anotherStringValue": "will not be masked"}',
                'maskStringWithSpaceAfterKey' =>
                    '{"stringValue" :"*REDACTED*","anotherStringValue" :"will not be masked"}',
                'maskStringWithBothSpaces' =>
                    '{"stringValue" : "*REDACTED*","anotherStringValue" : "will not be masked"}',
                'maskStringWithDoubleSpaces' =>
                    '{"stringValue"  :  "*REDACTED*","anotherStringValue"  :  "will not be masked"}',
                'maskStringWithEscaping' =>
                    '{\"stringValue\":\"*REDACTED*\",\"anotherStringValue\":\"will not be masked\"}',
                'maskStringWithOneKeyAndEscaping' => '{\"stringValue\":\"*REDACTED*\"}',
                'maskStringWithSpaceBeforeValueAndEscaping' =>
                    '{\"stringValue\": \"*REDACTED*\",\"anotherStringValue\": \"will not be masked\"}',
                'maskStringWithSpaceAfterKeyAndEscaping' =>
                    '{\"stringValue\" :\"*REDACTED*\",\"anotherStringValue\" :\"will not be masked\"}',
                'maskStringWithBothSpacesAndEscaping' =>
                    '{\"stringValue\" : \"*REDACTED*\",\"anotherStringValue\" : \"will not be masked\"}',
                'maskStringWithDoubleSpacesAndEscaping' =>
                    '{\"stringValue\"  :  \"*REDACTED*\",\"anotherStringValue\"  :  \"will not be masked\"}',
                // Integer
                'maskInteger' => '{"integerValue":"*REDACTED*","anotherIntegerValue":123}',
                'maskIntegerWithOneKey' => '{"integerValue":"*REDACTED*"}',
                'maskIntegerSpaceBeforeValue' => '{"integerValue": "*REDACTED*","anotherIntegerValue": 123}',
                'maskIntegerSpaceAfterKey' => '{"integerValue" :"*REDACTED*","anotherIntegerValue" :123}',
                'maskIntegerWithBothSpaces' => '{"integerValue" : "*REDACTED*","anotherIntegerValue" : 123}',
                'maskIntegerWithDoubleSpaces' => '{"integerValue"  :  "*REDACTED*","anotherIntegerValue"  :  123}',
                'maskIntegerWithEscaping' => '{\"integerValue\":\"*REDACTED*\",\"anotherIntegerValue\":123}',
                'maskIntegerWithOneKeyAndEscaping' => '{\"integerValue\":\"*REDACTED*\"}',
                'maskIntegerSpaceBeforeValueAndEscaping' =>
                    '{\"integerValue\": \"*REDACTED*\",\"anotherIntegerValue\": 123}',
                'maskIntegerSpaceAfterKeyAndEscaping' =>
                    '{\"integerValue\" :\"*REDACTED*\",\"anotherIntegerValue\" :123}',
                'maskIntegerWithBothSpacesAndEscaping' =>
                    '{\"integerValue\" : \"*REDACTED*\",\"anotherIntegerValue\" : 123}',
                'maskIntegerWithDoubleSpacesAndEscaping' =>
                    '{\"integerValue\"  :  \"*REDACTED*\",\"anotherIntegerValue\"  :  123}',
                // Double
                'maskDouble' => '{"doubleValue":"*REDACTED*","anotherDoubleValue":123.45}',
                'maskDoubleWithOneKey' => '{"doubleValue":"*REDACTED*"}',
                'maskDoubleSpaceBeforeValue' => '{"doubleValue": "*REDACTED*","anotherDoubleValue": 123.45}',
                'maskDoubleSpaceAfterKey' => '{"doubleValue" :"*REDACTED*","anotherDoubleValue" :123.45}',
                'maskDoubleWithBothSpaces' => '{"doubleValue" : "*REDACTED*","anotherDoubleValue" : 123.45}',
                'maskDoubleWithDoubleSpaces' => '{"doubleValue"  :  "*REDACTED*","anotherDoubleValue"  :  123.45}',
                'maskDoubleWithEscaping' => '{\"doubleValue\":\"*REDACTED*\",\"anotherDoubleValue\":123.45}',
                'maskDoubleWithOneKeyAndEscaping' => '{\"doubleValue\":\"*REDACTED*\"}',
                'maskDoubleSpaceBeforeValueAndEscaping' =>
                    '{\"doubleValue\": \"*REDACTED*\",\"anotherDoubleValue\": 123.45}',
                'maskDoubleSpaceAfterKeyAndEscaping' =>
                    '{\"doubleValue\" :\"*REDACTED*\",\"anotherDoubleValue\" :123.45}',
                'maskDoubleWithBothSpacesAndEscaping' =>
                    '{\"doubleValue\" : \"*REDACTED*\",\"anotherDoubleValue\" : 123.45}',
                'maskDoubleWithDoubleSpacesAndEscaping' =>
                    '{\"doubleValue\"  :  \"*REDACTED*\",\"anotherDoubleValue\"  :  123.45}',
                // Boolean
                'maskBoolean' => '{"booleanValue":"*REDACTED*","anotherBooleanValue":false}',
                'maskBooleanWithOneKey' => '{"booleanValue":"*REDACTED*"}',
                'maskBooleanSpaceBeforeValue' => '{"booleanValue": "*REDACTED*","anotherBooleanValue": false}',
                'maskBooleanSpaceAfterKey' => '{"booleanValue" :"*REDACTED*","anotherBooleanValue" :false}',
                'maskBooleanWithBothSpaces' => '{"booleanValue" : "*REDACTED*","anotherBooleanValue" : false}',
                'maskBooleanWithDoubleSpaces' => '{"booleanValue"  :  "*REDACTED*","anotherBooleanValue"  :  false}',
                'maskBooleanWithEscaping' => '{\"booleanValue\":\"*REDACTED*\",\"anotherBooleanValue\":false}',
                'maskBooleanWithOneKeyAndEscaping' => '{\"booleanValue\":\"*REDACTED*\"}',
                'maskBooleanSpaceBeforeValueAndEscaping' =>
                    '{\"booleanValue\": \"*REDACTED*\",\"anotherBooleanValue\": false}',
                'maskBooleanSpaceAfterKeyAndEscaping' =>
                    '{\"booleanValue\" :\"*REDACTED*\",\"anotherBooleanValue\" :false}',
                'maskBooleanWithBothSpacesAndEscaping' =>
                    '{\"booleanValue\" : \"*REDACTED*\",\"anotherBooleanValue\" : false}',
                'maskBooleanWithDoubleSpacesAndEscaping' =>
                    '{\"booleanValue\"  :  \"*REDACTED*\",\"anotherBooleanValue\"  :  false}',
                // Null
                'maskNull' => '{"nullValue":"*REDACTED*","anotherNullValue":null}',
                'maskNullWithOneKey' => '{"nullValue":"*REDACTED*"}',
                'maskNullSpaceBeforeValue' => '{"nullValue": "*REDACTED*","anotherNullValue": null}',
                'maskNullSpaceAfterKey' => '{"nullValue" :"*REDACTED*","anotherNullValue" :null}',
                'maskNullWithBothSpaces' => '{"nullValue" : "*REDACTED*","anotherNullValue" : null}',
                'maskNullWithDoubleSpaces' => '{"nullValue"  :  "*REDACTED*","anotherNullValue"  :  null}',
                'maskNullWithEscaping' => '{\"nullValue\":\"*REDACTED*\",\"anotherNullValue\":null}',
                'maskNullWithOneKeyAndEscaping' => '{\"nullValue\":\"*REDACTED*\"}',
                'maskNullSpaceBeforeValueAndEscaping' => '{\"nullValue\": \"*REDACTED*\",\"anotherNullValue\": null}',
                'maskNullSpaceAfterKeyAndEscaping' => '{\"nullValue\" :\"*REDACTED*\",\"anotherNullValue\" :null}',
                'maskNullWithBothSpacesAndEscaping' => '{\"nullValue\" : \"*REDACTED*\",\"anotherNullValue\" : null}',
                'maskNullWithDoubleSpacesAndEscaping' =>
                    '{\"nullValue\"  :  \"*REDACTED*\",\"anotherNullValue\"  :  null}',
                // Array
                'maskArray' => '{"arrayValue":["*REDACTED*"],"anotherArrayValue":["test"]}',
                'maskArrayWithOneKey' => '{"arrayValue":["*REDACTED*"]}',
                'maskArrayWithSpaceBeforeValue' => '{"arrayValue": ["*REDACTED*"],"anotherArrayValue": ["test"]}',
                'maskArrayWithSpaceAfterKey' => '{"arrayValue" :["*REDACTED*"],"anotherArrayValue" :["test"]}',
                'maskArrayWithBothSpaces' => '{"arrayValue" : ["*REDACTED*"],"anotherArrayValue" : ["test"]}',
                'maskArrayWithDoubleSpaces' => '{"arrayValue"  :  ["*REDACTED*"],"anotherArrayValue"  :  ["test"]}',
                'maskArrayWithEscaping' => '{\"arrayValue\":[\"*REDACTED*\"],\"anotherArrayValue\":[\"test\"]}',
                'maskArrayWithOneKeyAndEscaping' =>
                    '{\"arrayValue\":[\"*REDACTED*\"],\"anotherArrayValue\":[\"test\"]}',
                'maskArrayWithSpaceBeforeValueAndEscaping' =>
                    '{\"arrayValue\": [\"*REDACTED*\"],\"anotherArrayValue\": [\"test\"]}',
                'maskArrayWithSpaceAfterKeyAndEscaping' =>
                    '{\"arrayValue\" :[\"*REDACTED*\"],\"anotherArrayValue\" :[\"test\"]}',
                'maskArrayWithBothSpacesAndEscaping' =>
                    '{\"arrayValue\" : [\"*REDACTED*\"],\"anotherArrayValue\" : [\"test\"]}',
                'maskArrayWithDoubleSpacesAndEscaping' =>
                    '{\"arrayValue\"  :  [\"*REDACTED*\"],\"anotherArrayValue\"  :  [\"test\"]}',
                // Object
                'maskObject' => '{"objectValue":"*REDACTED*","anotherObjectValue":{"foo":"bar"}}',
                'maskObjectWithOneKey' => '{"objectValue":"*REDACTED*"}',
                'maskObjectSpaceBeforeValue' => '{"objectValue": "*REDACTED*","anotherObjectValue": {"foo":"bar"}}',
                'maskObjectSpaceAfterKey' => '{"objectValue" :"*REDACTED*","anotherObjectValue" :{"foo":"bar"}}',
                'maskObjectWithBothSpaces' => '{"objectValue" : "*REDACTED*","anotherObjectValue" : {"foo":"bar"}}',
                'maskObjectWithDoubleSpaces' =>
                    '{"objectValue"  :  "*REDACTED*","anotherObjectValue"  :  {"foo":"bar"}}',
                'maskObjectWithEscaping' => '{\"objectValue\":\"*REDACTED*\",\"anotherObjectValue\":{\"foo\":\"bar\"}}',
                'maskObjectWithOneKeyAndEscaping' => '{\"objectValue\":\"*REDACTED*\"}',
                'maskObjectSpaceBeforeValueAndEscaping' =>
                    '{\"objectValue\": \"*REDACTED*\",\"anotherObjectValue\": {\"foo\":\"bar\"}}',
                'maskObjectSpaceAfterKeyAndEscaping' =>
                    '{\"objectValue\" :\"*REDACTED*\",\"anotherObjectValue\" :{\"foo\":\"bar\"}}',
                'maskObjectWithBothSpacesAndEscaping' =>
                    '{\"objectValue\" : \"*REDACTED*\",\"anotherObjectValue\" : {\"foo\":\"bar\"}}',
                'maskObjectWithDoubleSpacesAndEscaping' =>
                    '{\"objectValue\"  :  \"*REDACTED*\",\"anotherObjectValue\"  :  {\"foo\":\"bar\"}}',
                // Nested
                'maskNested' =>
                    '{"test":{"stringValue":"*REDACTED*","integerValue":"*REDACTED*","doubleValue":"*REDACTED*",' .
                    '"booleanValue":"*REDACTED*","nullValue":"*REDACTED*","arrayValue":["*REDACTED*"],' .
                    '"objectValue":"*REDACTED*"}}',
                'maskNestedWithEscaping' =>
                    '{\"test\":{\"stringValue\":\"*REDACTED*\",\"integerValue\":\"*REDACTED*\",' .
                    '\"doubleValue\":\"*REDACTED*\",\"booleanValue\":\"*REDACTED*\",' .
                    '\"nullValue\":\"*REDACTED*\",\"arrayValue\":[\"*REDACTED*\"],\"objectValue\":\"*REDACTED*\"}}',
            ],
            'maskKeys' => [
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

    public static function provideExceptionDataForSanitizing(): iterable
    {
        yield 'Mask value if key explicitly provided' => [
            'keysToMask' => ['message'],
            'message' => 'This is a test message',
            'expectedMessage' => '*REDACTED*',
        ];
        yield 'Mask key in JSON' => [
            'keysToMask' => ['keyToMask'],
            'message' => '{"keyToMask":"This is a test message"}',
            'expectedMessage' => '{"keyToMask":"*REDACTED*"}',
        ];
        yield 'Mask card number' => [
            'keysToMask' => ['card'],
            'message' => 'This is a test message with card number 4242 4242 4242 4242',
            'expectedMessage' => 'This is a test message with card number 424242*REDACTED*4242',
        ];
    }

    public function testDoNotSanitizeCardLikeValuesThatAreNotLuhnValid(): void
    {
        $sanitizer = $this->getSanitizer();

        $result = $sanitizer->sanitize(['uuid' => '{"uuid":"ba7e6152-1756-4391-bbe6-3bbc5057eb3d"}']);

        self::assertEquals(['uuid' => '{"uuid":"ba7e6152-1756-4391-bbe6-3bbc5057eb3d"}'], $result);
    }

    /**
     * @param string[]|null $keysToMask
     */
    #[DataProvider('provideAbstractDataForSanitizing')]
    public function testSanitize(array $input, array $expectedOutput, ?array $keysToMask = null): void
    {
        $sanitizer = $this->getSanitizer($keysToMask);

        self::assertEquals($expectedOutput, $sanitizer->sanitize($input));
    }

    /**
     * @param string[] $keysToMask
     */
    #[DataProvider('provideExceptionDataForSanitizing')]
    public function testSanitizeException(array $keysToMask, string $message, string $expectedMessage): void
    {
        $sanitizer = $this->getSanitizer($keysToMask);
        $exception = new RuntimeException($message);

        $sanitizedException = $sanitizer->sanitize($exception);

        self::assertSame($expectedMessage, $sanitizedException['message']);
    }

    abstract protected function getSanitizer(?array $keysToMask = null): SensitiveDataSanitizerInterface;
}
