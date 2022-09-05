<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Response;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseDataInterface;
use EonX\EasyErrorHandler\Response\Data\ErrorResponseData;
use EonX\EasyErrorHandler\Response\SymfonySerializerResponseFactory;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonySerializerResponseFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testCreate
     */
    public function providerTestCreate(): iterable
    {
        yield 'Default format' => [
            'request' => new Request(),
            'errorResponseData' => ErrorResponseData::create(['message' => 'yeah']),
            'serializer' => new Serializer([], [new JsonEncoder()]),
            'errorFormats' => [],
            'expectedContent' => '{"message":"yeah"}',
        ];

        $request = new Request();
        $request->setRequestFormat('application/nathan+xml');

        yield 'Xml format' => [
            'request' => $request,
            'errorResponseData' => ErrorResponseData::create(['message' => 'yeah']),
            'serializer' => new Serializer([], [new XmlEncoder()]),
            'errorFormats' => [
                'xml' => ['application/nathan+xml'],
            ],
            'expectedContent' => "<?xml version=\"1.0\"?>\n<response><message>yeah</message></response>\n",
        ];
    }

    /**
     * @param mixed[] $errorFormats
     *
     * @dataProvider providerTestCreate
     */
    public function testCreate(
        Request $request,
        ErrorResponseDataInterface $errorResponseData,
        SerializerInterface $serializer,
        array $errorFormats,
        string $expectedContent
    ): void {
        $responseFactory = new SymfonySerializerResponseFactory($serializer, $errorFormats);

        $response = $responseFactory->create($request, $errorResponseData);

        self::assertSame($expectedContent, $response->getContent());
    }
}
