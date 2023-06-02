<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginatorInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilderTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testCreateFromRequest
     */
    public function providerTestCreateFromRequest(): iterable
    {
        yield 'Group not added, both type and request method invalid' => [
            'contextFromDecorated' => [
                'operation_type' => 'invalid',
            ],
            'requestMethod' => 'INVALID',
            'groupAdded' => false,
        ];

        yield 'Group not added, type invalid' => [
            'contextFromDecorated' => [
                'operation_type' => 'invalid',
            ],
            'requestMethod' => 'GET',
            'groupAdded' => false,
        ];

        yield 'Group not added, request method invalid' => [
            'contextFromDecorated' => [
                'operation_type' => CustomPaginatorInterface::OPERATION_TYPE,
            ],
            'requestMethod' => 'INVALID',
            'groupAdded' => false,
        ];

        yield 'Group added' => [
            'contextFromDecorated' => [
                'operation_type' => CustomPaginatorInterface::OPERATION_TYPE,
            ],
            'requestMethod' => 'GET',
            'groupAdded' => true,
        ];
    }

    /**
     * @param mixed[] $contextFromDecorated
     *
     * @dataProvider providerTestCreateFromRequest
     */
    public function testCreateFromRequest(array $contextFromDecorated, string $requestMethod, bool $groupAdded): void
    {
        $request = new Request();
        $request->setMethod($requestMethod);
        $contextBuilder = new SerializerContextBuilder(
            $this->mockDecoratedSerializerContextBuilder($contextFromDecorated),
        );

        $context = $contextBuilder->createFromRequest($request, true);

        $inArray = \in_array(CustomPaginatorInterface::SERIALIZER_GROUP, $context['groups'] ?? [], true);
        self::assertEquals($groupAdded, $inArray);
    }

    /**
     * @param mixed[] $contextFromDecorated
     *
     * @return \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface
     */
    private function mockDecoratedSerializerContextBuilder(
        array $contextFromDecorated,
    ): SerializerContextBuilderInterface {
        /** @var \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface $decorated */
        $decorated = $this->mock(
            SerializerContextBuilderInterface::class,
            static function (MockInterface $mock) use ($contextFromDecorated): void {
                $mock
                    ->shouldReceive('createFromRequest')
                    ->once()
                    ->andReturn($contextFromDecorated);
            },
        );

        return $decorated;
    }
}
