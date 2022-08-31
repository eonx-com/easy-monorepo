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
            new Request(),
            ErrorResponseData::create([
                'message' => 'yeah',
            ]),
            '{"message":"yeah"}',
        ];

        $request = new Request();
        $request->setRequestFormat('application/nathan+xml');

        yield 'Xml format' => [
            $request,
            ErrorResponseData::create([
                'message' => 'yeah',
            ]),
            '<?xml version="1.0"?><response><message>yeah</message></response>',
            new Serializer([], [new XmlEncoder()]),
            [
                'xml' => ['application/nathan+xml'],
            ],
        ];
    }

    /**
     * @param mixed[] $errorFormats
     *
     * @dataProvider providerTestCreate
     */
    public function testCreate(
        Request $request,
        ErrorResponseDataInterface $data,
        string $content,
        ?SerializerInterface $serializer = null,
        array $errorFormats
    ): void {
        $serializer = $serializer ?? new Serializer([], [new JsonEncoder()]);
        $responseFactory = new SymfonySerializerResponseFactory($serializer, $errorFormats);

        $response = $responseFactory->create($request, $data);

        self::assertSame($this->removeEndOfLines($content), $this->removeEndOfLines((string)$response->getContent()));
    }

    private function removeEndOfLines(string $content): string
    {
        return \str_replace(\PHP_EOL, '', $content);
    }
}
