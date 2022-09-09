<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OpenApiContextProvider
{
    /**
     * @var mixed[]
     */
    private array $contexts;

    /**
     * @var mixed[]
     */
    private array $contextsCache = [];

    /**
     * @var mixed[]
     */
    private array $defaults;

    public function __construct(
        string $openApiContextsFile,
        private ValidatorInterface $validator
    ) {
        $openApiContexts = require $openApiContextsFile;

        $this->validate($openApiContexts);

        $this->defaults = $openApiContexts['defaults'];
        $this->contexts = $openApiContexts['openapi_contexts'];
    }

    /**
     * @param mixed[] $schema
     *
     * @return mixed[]
     */
    public function getCustomSchemaProperties(string $schemaName, array $schema): array
    {
        $resourceName = \strtok($schemaName, '.-');
        $customSchema = $this->contexts[(string)$resourceName]['schema'][$schemaName] ?? null;

        /** @noinspection SlowArrayOperationsInLoopInspection */
        return \is_array($customSchema)
            ? \array_replace_recursive($schema, $customSchema)
            : $schema;
    }

    public function getOperationSummary(string $resourceName, string $applyTo, string $operationName): string
    {
        return $this->getOpenApiContext($resourceName)[$applyTo][$operationName]['summary'];
    }

    /**
     * @return array<int, string>
     */
    public function getResponsesDescriptions(string $resourceName, string $applyTo, string $operationName): array
    {
        return $this->getOpenApiContext($resourceName)[$applyTo][$operationName]['responses'] ?? [];
    }

    public function getTag(string $resourceName): string
    {
        return $this->contexts[$resourceName]['tag'] ?? '';
    }

    /**
     * @return mixed[]
     */
    private function getOpenApiContext(string $resourceName): array
    {
        if (isset($this->contextsCache[$resourceName])) {
            return $this->contextsCache[$resourceName];
        }

        $openApiContext = \array_replace_recursive(
            $this->defaults,
            $this->contexts[$resourceName]
        );

        $this->hydratePlaceholders($openApiContext['collection'], $openApiContext['resource']);
        $this->hydratePlaceholders($openApiContext['item'], $openApiContext['resource']);

        $this->contextsCache[$resourceName] = $openApiContext;

        return $this->contextsCache[$resourceName];
    }

    /**
     * @param mixed[] $operations
     */
    private function hydratePlaceholders(array &$operations, string $resource): void
    {
        foreach ($operations as &$attributes) {
            if (isset($attributes['summary'])) {
                $attributes['summary'] = \str_replace(
                    [
                        '$resource',
                        '$Resource',
                    ],
                    [
                        $resource,
                        \ucfirst($resource),
                    ],
                    (string)$attributes['summary']
                );
            }
            if (isset($attributes['responses'])) {
                foreach ($attributes['responses'] as &$description) {
                    $description = \str_replace(
                        [
                            '$resource',
                            '$Resource',
                        ],
                        [
                            $resource,
                            \ucfirst($resource),
                        ],
                        (string)$description
                    );
                }
            }
        }
    }

    private function prepareConstraints(): Assert\Collection
    {
        $itemConstraints = new Assert\All([
            new Assert\Type('array'),
            new Assert\Collection([
                'responses' => [
                    new Assert\Type('array'),
                    new Assert\Count(['min' => 1]),
                ],
                'summary' => [
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ],
            ]),
        ]);
        $itemConstraintsOptional = new Assert\All([
            new Assert\Type('array'),
            new Assert\Collection([
                'responses' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\Count(['min' => 1]),
                ]),
                'summary' => new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ]),
            ]),
        ]);

        return new Assert\Collection([
            'defaults' => new Assert\Collection([
                'collection' => $itemConstraints,
                'item' => $itemConstraints,
            ]),
            'openapi_contexts' => new Assert\All([
                new Assert\Type('array'),
                new Assert\Collection([
                    'fields' => [
                        'resource' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'tag' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'schema' => [
                            new Assert\Type('array'),
                        ],
                        'collection' => $itemConstraintsOptional,
                        'item' => $itemConstraintsOptional,
                    ],
                    'allowExtraFields' => true,
                    'allowMissingFields' => true,
                ]),
            ]),
        ]);
    }

    /**
     * @param array<mixed> $openApiContexts
     */
    private function validate(array $openApiContexts): void
    {
        $constraints = $this->prepareConstraints();

        $violations = $this->validator->validate($openApiContexts, $constraints);

        if ($violations->count() > 0) {
            throw new ValidationFailedException('open_api_contexts', $violations);
        }
    }
}
