<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryAwareInterface;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;

final readonly class PaginationSchemaFactory implements SchemaFactoryInterface, SchemaFactoryAwareInterface
{
    public function __construct(
        private SchemaFactoryInterface $decorated,
    ) {
    }

    public function buildSchema(
        string $className,
        string $format = 'json',
        string $type = Schema::TYPE_OUTPUT,
        ?Operation $operation = null,
        ?Schema $schema = null,
        ?array $serializerContext = null,
        bool $forceCollection = false,
    ): Schema {
        $schema = $this->decorated->buildSchema(
            $className,
            $format,
            $type,
            $operation,
            $schema,
            $serializerContext,
            $forceCollection
        );

        if ($format !== 'json') {
            return $schema;
        }

        if ($forceCollection) {
            $schema['type'] = 'object';
            $schema['properties'] = [
                'items' => [
                    'items' => $schema['items'],
                    'type' => 'array',
                ],
                'pagination' => [
                    'properties' => [
                        'currentPage' => [
                            'minimum' => 1,
                            'type' => 'integer',
                        ],
                        'hasNextPage' => [
                            'type' => 'boolean',
                        ],
                        'hasPreviousPage' => [
                            'type' => 'boolean',
                        ],
                        'itemsPerPage' => [
                            'type' => 'integer',
                        ],
                        'totalItems' => [
                            'minimum' => 0,
                            'type' => 'integer',
                        ],
                        'totalPages' => [
                            'minimum' => 0,
                            'type' => 'integer',
                        ],
                    ],
                    'type' => 'object',
                ],
            ];

            unset($schema['items']);
        }

        return $schema;
    }

    public function setSchemaFactory(SchemaFactoryInterface $schemaFactory): void
    {
        if ($this->decorated instanceof SchemaFactoryAwareInterface) {
            $this->decorated->setSchemaFactory($schemaFactory);
        }
    }
}
