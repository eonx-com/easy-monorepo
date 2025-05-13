<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryAwareInterface;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginationOptions;

final readonly class PaginationSchemaFactory implements SchemaFactoryInterface, SchemaFactoryAwareInterface
{
    public function __construct(
        private SchemaFactoryInterface $decorated,
        private ?PaginationOptions $paginationOptions = new PaginationOptions(),
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
            $itemsPerPageSchema = [
                'default' => $operation?->getPaginationItemsPerPage()
                    ?? $this->paginationOptions->getItemsPerPage(),
                'type' => 'integer',
                'minimum' => 0,
            ];

            $itemsPerPageMaximum = $operation?->getPaginationMaximumItemsPerPage()
                ?? $this->paginationOptions->getMaximumItemsPerPage();

            if ($itemsPerPageMaximum !== null) {
                $itemsPerPageSchema['maximum'] = $itemsPerPageMaximum;
            }

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
                        'itemsPerPage' => $itemsPerPageSchema,
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
