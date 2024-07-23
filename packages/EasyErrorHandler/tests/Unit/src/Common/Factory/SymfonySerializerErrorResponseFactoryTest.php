<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Factory;

use EonX\EasyErrorHandler\Common\Factory\SymfonySerializerErrorResponseFactory;
use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseData;
use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseDataInterface;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonySerializerErrorResponseFactoryTest extends AbstractUnitTestCase
{
    /**
     * @see testCreate
     */
    public static function provideCreateData(): iterable
    {
        yield 'Default format' => [
            'request' => new Request(),
            'errorResponseData' => ErrorResponseData::create(['message' => 'yeah']),
            'serializer' => new Serializer([], [new JsonEncoder()]),
            'expectedContent' => '{"message":"yeah"}',
            'errorFormats' => null,
        ];

        $request = new Request();
        $request->setRequestFormat('application/nathan+xml');

        yield 'Xml format' => [
            'request' => $request,
            'errorResponseData' => ErrorResponseData::create(['message' => 'yeah']),
            'serializer' => new Serializer([], [new XmlEncoder()]),
            'expectedContent' => "<?xml version=\"1.0\"?>\n<response><message>yeah</message></response>\n",
            'errorFormats' => [
                'xml' => ['application/nathan+xml'],
            ],
        ];
    }

    #[DataProvider('provideCreateData')]
    public function testCreate(
        Request $request,
        ErrorResponseDataInterface $errorResponseData,
        SerializerInterface $serializer,
        string $expectedContent,
        ?array $errorFormats = null,
    ): void {
        $responseFactory = new SymfonySerializerErrorResponseFactory($serializer, $errorFormats);

        $response = $responseFactory->create($request, $errorResponseData);

        self::assertSame($expectedContent, $response->getContent());
    }
}
