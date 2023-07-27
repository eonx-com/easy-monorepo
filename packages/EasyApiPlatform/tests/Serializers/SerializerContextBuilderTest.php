<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Serializers;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use EonX\EasyApiPlatform\Paginators\CustomPaginatorInterface;
use EonX\EasyApiPlatform\Serializers\SerializerContextBuilder;
use EonX\EasyApiPlatform\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilderTest extends AbstractTestCase
{
    /**
     * @see testCreateFromRequest
     */
    public static function providerTestCreateFromRequest(): iterable
    {
        yield 'Group is not added because an operation is not GetCollection' => [
            'contextFromDecorated' => [
                'operation' => new Get(),
            ],
            'isGroupAdded' => false,
        ];

        yield 'Group is added' => [
            'contextFromDecorated' => [
                'operation' => new GetCollection(),
            ],
            'isGroupAdded' => true,
        ];
    }

    /**
     * @dataProvider providerTestCreateFromRequest
     */
    public function testCreateFromRequest(array $contextFromDecorated, bool $isGroupAdded): void
    {
        $request = new Request();
        $contextBuilder = new SerializerContextBuilder(
            $this->mockDecoratedSerializerContextBuilder($contextFromDecorated)
        );

        $context = $contextBuilder->createFromRequest($request, true);

        self::assertSame(
            $isGroupAdded,
            \in_array(CustomPaginatorInterface::SERIALIZER_GROUP, $context['groups'] ?? [], true)
        );
    }

    private function mockDecoratedSerializerContextBuilder(
        array $contextFromDecorated,
    ): SerializerContextBuilderInterface {
        /** @var \ApiPlatform\Serializer\SerializerContextBuilderInterface $decorated */
        $decorated = self::mock(
            SerializerContextBuilderInterface::class,
            static function (MockInterface $mock) use ($contextFromDecorated): void {
                $mock
                    ->shouldReceive('createFromRequest')
                    ->once()
                    ->andReturn($contextFromDecorated);
            }
        );

        return $decorated;
    }
}
