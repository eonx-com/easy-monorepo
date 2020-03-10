<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use ApiPlatform\Core\Serializer\SerializerContextBuilder as ApiPlatformSerializerContextBuilder;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginatorInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilderTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestCreateFromRequest(): iterable
    {
        yield 'Group not added, both type and name invalid' => [
            ['operation_type' => 'invalid', 'collection_operation_name' => 'invalid'],
            false,
        ];

        yield 'Group not added, type invalid' => [
            ['operation_type' => 'invalid', 'item_operation_name' => CustomPaginatorInterface::OPERATION_NAME],
            false,
        ];

        yield 'Group not added, name invalid' => [
            ['operation_type' => CustomPaginatorInterface::OPERATION_TYPE, 'collection_operation_name' => 'invalid'],
            false,
        ];

        yield 'Group added' => [
            [
                'operation_type' => CustomPaginatorInterface::OPERATION_TYPE,
                'collection_operation_name' => CustomPaginatorInterface::OPERATION_NAME,
            ],
            true,
        ];
    }

    /**
     * @param mixed[] $context
     *
     * @dataProvider providerTestCreateFromRequest
     */
    public function testCreateFromRequest(array $context, bool $groupAdded): void
    {
        $context = \array_merge($context, ['resource_class' => 'class']);

        $contextBuilder = new SerializerContextBuilder($this->getApiPlatformSerializerContextBuilder());

        $context = $contextBuilder->createFromRequest(new Request(), true, $context);
        $inArray = \in_array(CustomPaginatorInterface::SERIALIZER_GROUP, $context['groups'] ?? [], true);

        self::assertEquals($groupAdded, $inArray);
    }

    private function getApiPlatformSerializerContextBuilder(): ApiPlatformSerializerContextBuilder
    {
        /** @var \ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface $metadataFactory */
        $metadataFactory = $this->mock(
            ResourceMetadataFactoryInterface::class,
            static function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('create')
                    ->once()
                    ->with('class')
                    ->andReturn(new ResourceMetadata(null, null, null, []));
            }
        );

        return new ApiPlatformSerializerContextBuilder($metadataFactory);
    }
}
