<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor;

use Carbon\CarbonImmutable;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider\OpenApiContextProvider;

final class UpdateDocsComponentsSchemasProcessor implements
    DecorationProcessorInterface,
    DefaultDecorationProcessorInterface
{
    public function __construct(private OpenApiContextProvider $openApiContextProvider)
    {
    }

    public function process(array $documentation): array
    {
        $schema = [];

        foreach ($documentation['components']['schemas'] as $schemaName => &$schema) {
            if (isset($schema['properties']) === false) {
                continue;
            }

            $this->updateDocsPropertyDescriptions($schema['properties']);

            $this->updateDocsPropertyLinks($schema['properties']);

            $schema = $this->openApiContextProvider->getCustomSchemaProperties($schemaName, $schema);
        }
        unset($schema);

        return $documentation;
    }

    /**
     * @param mixed[] $properties
     */
    private function updateDocsPropertyDescriptions(array &$properties): void
    {
        $property = [];

        foreach ($properties as $propertyName => &$property) {
            // API Platform doesn't use the embedded descriptions for createdAt/updatedAt
            if ($propertyName === 'createdAt') {
                $property['description'] = 'Timestamp of resource creation.';
            }
            if ($propertyName === 'updatedAt') {
                $property['description'] = 'Timestamp of the most recent resource update.';
            }

            // Remove newlines in property descriptions (except for before list items)
            if (isset($property['description'])) {
                $property['description'] = \preg_replace('/\\n([^-])/', ' $1', $property['description']);
            }
        }

        unset($property);
    }

    /**
     * @param mixed[] $properties
     */
    private function updateDocsPropertyLinks(array &$properties): void
    {
        $property = [];
        $dateTimeExample = CarbonImmutable::now()->toIso8601String();

        foreach ($properties as &$property) {
            // Remove 'anyOf' tags in schemas and replace with a direct link to the referred schema
            if (isset($property['anyOf'])) {
                $property['$ref'] = $property['anyOf'][0]['$ref'];
                unset($property['anyOf']);
            }

            // Remove 'oneOf' tags in schemas and replace with first item in array
            if (isset($property['oneOf'])) {
                $property = $property['oneOf'][0];
            }

            unset($property['readOnly']);

            if (isset($property['format']) && $property['format'] === 'date-time') {
                $property['example'] = $property['example'] ?? $dateTimeExample;
            }

            if (isset($property['$ref']) && \str_contains((string)$property['$ref'], '/schemas/Number')) {
                $property['type'] = 'string';
                $property['example'] = '1000';
                unset($property['$ref']);
            }
        }

        unset($property);
    }
}
