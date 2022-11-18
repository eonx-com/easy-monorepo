<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * This class allows us to define multiple search strategy for the same property on a single resource.
 *
 * For API Platform >= 2.7.0 use AdvancedSearchFilter
 *
 * @see \EonX\EasyApiPlatform\Filter\AdvancedSearchFilter
 *
 * The default behaviour of SearchFilter is preserved + ability to define "virtual properties" mapped to real ones:
 *
 * ApiFilter(
 *     VirtualSearchFilter::class,
 *     properties={
 *         "number[partial]": {"number": "ipartial"}, --> Virtual search property to number partial
 *         "number[exact]": {"number": "exact"}, --> Virtual search property to number exact
 *         "email": "ipartial" --> Normal search property definition
 *     }
 * )
 */
final class VirtualSearchFilter extends SearchFilter
{
    /**
     * @var mixed[]
     */
    private $virtualProperties;

    /**
     * @return mixed[]
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $properties = $this->getProperties();

        if (null === $properties) {
            $properties = \array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $strategy) {
            $virtualProperty = $property;

            if (\is_array($strategy)) {
                $property = \array_keys($strategy)[0];
                $strategy = $strategy[$property];
            }

            if ($this->isPropertyMapped($property, $resourceClass, true) === false) {
                continue;
            }

            if ($this->isPropertyNested($property, $resourceClass)) {
                $propertyParts = $this->splitPropertyParts($property, $resourceClass);
                $field = $propertyParts['field'];
                $metadata = $this->getNestedMetadata($resourceClass, $propertyParts['associations']);
            } else {
                $field = $property;
                $metadata = $this->getClassMetadata($resourceClass);
            }

            $propertyName = $this->normalizePropertyName($virtualProperty);
            if ($metadata->hasField($field)) {
                $typeOfField = $this->getType($metadata->getTypeOfField($field));
                $strategy = $strategy ?? self::STRATEGY_EXACT;
                $filterParameterNames = [$propertyName];

                if (self::STRATEGY_EXACT === $strategy) {
                    $filterParameterNames[] = $propertyName . '[]';
                }

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => $typeOfField,
                        'required' => false,
                        'strategy' => $strategy,
                        'is_collection' => '[]' === \substr((string)$filterParameterName, -2),
                    ];
                }
            } elseif ($metadata->hasAssociation($field)) {
                $filterParameterNames = [
                    $propertyName,
                    $propertyName . '[]',
                ];

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => 'string',
                        'required' => false,
                        'strategy' => self::STRATEGY_EXACT,
                        'is_collection' => '[]' === \substr((string)$filterParameterName, -2),
                    ];
                }
            }
        }

        return $description;
    }

    /**
     * @param mixed $value
     * @param null|mixed[] $context
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        ?array $context = null
    ) {
        // Keep virtual properties on private property because parent uses `$properties`
        if ($this->virtualProperties === null) {
            $this->virtualProperties = $this->properties ?? [];
        }

        // Allow square brackets in filter property
        if (\is_array($value)) {
            $property .= '[' . \array_keys($value)[0] . ']';
            $value = \array_values($value)[0];
        }

        $props = $this->virtualProperties[$property] ?? null;

        // If property doesn't mapped, abort, and accept only string or array
        if ($props === null || (\is_string($props) === false && \is_array($props) === false)) {
            return;
        }

        // Allow to configure normal search as <property> => <strategy>
        if (\is_string($props)) {
            $this->properties = [$property => $props];
        }

        // Allow to configure virtual search as <virtual_property> => [<property> => <strategy>]
        if (\is_array($props)) {
            $this->properties = $props;
            $property = \array_keys($props)[0];
        }

        parent::filterProperty(
            $property,
            $value,
            $queryBuilder,
            $queryNameGenerator,
            $resourceClass,
            $operationName
        );
    }
}
