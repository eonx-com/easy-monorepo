<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Filter;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Closure;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Filter the collection by given properties.
 * This filter allows to define multiple search strategy for the same property on a single resource.
 *
 * #[ApiFilter(
 *     AdvancedSearchFilter::class,
 *     properties: [
 *         'email' => 'iexact', // Normal search property definition
 *         'description[exact]' => ['description' => 'exact'], // Filter for description field with exact strategy
 *         'description[partial]' => ['description' => 'partial'], // Filter for description field with partial strategy
 *     ]
 * )]
 */
final class AdvancedSearchFilter extends AbstractFilter implements SearchFilterInterface
{
    use PropertyHelperTrait;

    public const DOCTRINE_INTEGER_TYPE = Types::INTEGER;

    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @param mixed[] $properties
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly IriConverterInterface $iriConverter,
        PropertyAccessorInterface $propertyAccessor = null,
        LoggerInterface $logger = null,
        array $properties = null,
        NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);

        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return mixed[]
     *
     * {@inheritDoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();
        if ($properties === null) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $strategy) {
            $filterParameter = $property;

            if (is_array($strategy)) {
                $property = array_keys($strategy)[0];
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

            $propertyName = $this->normalizePropertyName($property);
            $filterParameterName = $this->normalizePropertyName($filterParameter);
            if ($metadata->hasField($field)) {
                $typeOfField = $this->getType($metadata->getTypeOfField($field));
                $strategy = $strategy ?? self::STRATEGY_EXACT;
                $filterParameterNames = [$filterParameterName];

                if ($strategy === self::STRATEGY_EXACT) {
                    $filterParameterNames[] = $filterParameterName . '[]';
                }

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => $typeOfField,
                        'required' => false,
                        'strategy' => $strategy,
                        'is_collection' => str_ends_with($filterParameterName, '[]'),
                    ];
                }
            } elseif ($metadata->hasAssociation($field)) {
                $filterParameterNames = [
                    $filterParameterName,
                    $filterParameterName . '[]',
                ];

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => 'string',
                        'required' => false,
                        'strategy' => self::STRATEGY_EXACT,
                        'is_collection' => str_ends_with($filterParameterName, '[]'),
                    ];
                }
            }
        }

        return $description;
    }

    /**
     * Adds where clause according to the strategy.
     *
     * @throws InvalidArgumentException If strategy does not exist
     */
    protected function addWhereByStrategy(
        string $strategy,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $field,
        mixed $values,
        bool $caseSensitive
    ): void {
        if (is_array($values) === false) {
            $values = [$values];
        }

        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = ':' . $queryNameGenerator->generateParameterName($field);
        $aliasedField = sprintf('%s.%s', $alias, $field);

        if ($strategy === '' || $strategy === self::STRATEGY_EXACT) {
            if (count($values) === 1) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($wrapCase($aliasedField), $wrapCase($valueParameter)))
                    ->setParameter($valueParameter, $values[0]);

                return;
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in($wrapCase($aliasedField), $valueParameter))
                ->setParameter($valueParameter, $caseSensitive ? $values : array_map('strtolower', $values));

            return;
        }

        $ors = [];
        $parameters = [];
        foreach ($values as $key => $value) {
            $keyValueParameter = sprintf('%s_%s', $valueParameter, $key);
            $parameters[$caseSensitive ? $value : strtolower($value)] = $keyValueParameter;

            $ors[] = match ($strategy) {
                self::STRATEGY_PARTIAL => $queryBuilder->expr()->like(
                    $wrapCase($aliasedField),
                    $wrapCase((string)$queryBuilder->expr()->concat("'%'", $keyValueParameter, "'%'"))
                ),
                self::STRATEGY_START => $queryBuilder->expr()->like(
                    $wrapCase($aliasedField),
                    $wrapCase((string)$queryBuilder->expr()->concat($keyValueParameter, "'%'"))
                ),
                self::STRATEGY_END => $queryBuilder->expr()->like(
                    $wrapCase($aliasedField),
                    $wrapCase((string)$queryBuilder->expr()->concat("'%'", $keyValueParameter))
                ),
                self::STRATEGY_WORD_START => $queryBuilder->expr()->orX(
                    $queryBuilder->expr()
                        ->like(
                            $wrapCase($aliasedField),
                            $wrapCase((string)$queryBuilder->expr()->concat($keyValueParameter, "'%'"))
                        ),
                    $queryBuilder->expr()
                        ->like(
                            $wrapCase($aliasedField),
                            $wrapCase((string)$queryBuilder->expr()->concat("'% '", $keyValueParameter, "'%'"))
                        )
                ),
                default => throw new InvalidArgumentException(sprintf('strategy %s does not exist.', $strategy)),
            };
        }

        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$ors));
        array_walk($parameters, [$queryBuilder, 'setParameter']);
    }

    /**
     * Creates a function that will wrap a Doctrine expression according to the
     * specified case sensitivity.
     *
     * For example, "o.name" will get wrapped into "LOWER(o.name)" when $caseSensitive
     * is false.
     */
    protected function createWrapCase(bool $caseSensitive): Closure
    {
        return static function (string $expr) use ($caseSensitive): string {
            if ($caseSensitive) {
                return $expr;
            }

            return sprintf('LOWER(%s)', $expr);
        };
    }

    /**
     * @param mixed[] $context
     *
     * {@inheritdoc}
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $filterParameter = $property;
        if (isset($this->properties[$filterParameter]) && is_array($this->properties[$filterParameter])) {
            $property = (string)array_keys($this->properties[$filterParameter])[0];
            $strategy = array_values($this->properties[$filterParameter])[0];
        }

        if (is_array($value) && count($value) > 0) {
            $valueKey = array_keys($value)[0];
            $possibleFilterParameter = $filterParameter . '[' . $valueKey . ']';

            if (
                isset($this->properties[$possibleFilterParameter])
                && is_array($this->properties[$possibleFilterParameter])
            ) {
                $filterParameter = $possibleFilterParameter;

                $property = (string)array_keys($this->properties[$filterParameter])[0];
                $strategy = array_values($this->properties[$filterParameter])[0];

                $value = $value[$valueKey];
            }
        }

        if (
            $value === null ||
            $this->isPropertyEnabled($filterParameter, $resourceClass) === false ||
            $this->isPropertyMapped($property, $resourceClass, true) === false
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        $values = $this->normalizeValues((array)$value, $property);
        if ($values === null) {
            return;
        }

        $associations = [];
        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field, $associations] = $this->addJoinsForNestedProperty(
                $property,
                $alias,
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                Join::INNER_JOIN
            );
        }

        $caseSensitive = true;
        $strategy = $strategy ?? ($this->properties[$filterParameter] ?? self::STRATEGY_EXACT);

        // prefixing the strategy with i makes it case-insensitive
        if (str_starts_with($strategy, 'i')) {
            $strategy = substr($strategy, 1);
            $caseSensitive = false;
        }

        $metadata = $this->getNestedMetadata($resourceClass, $associations);

        if ($metadata->hasField($field)) {
            if ($field === 'id') {
                $values = array_map([$this, 'getIdFromValue'], $values);
            }

            if ($this->hasValidValues($values, $this->getDoctrineFieldType($property, $resourceClass)) === false) {
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(sprintf(
                        'Values for field "%s" are not valid according to the doctrine type.',
                        $field
                    )),
                ]);

                return;
            }

            $this->addWhereByStrategy(
                $strategy,
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                $values,
                $caseSensitive
            );

            return;
        }

        // metadata doesn't have the field, nor an association on the field
        if ($metadata->hasAssociation($field) === false) {
            return;
        }

        $values = array_map([$this, 'getIdFromValue'], $values);

        $associationResourceClass = (string)$metadata->getAssociationTargetClass($field);
        $associationFieldIdentifier = $metadata->getIdentifierFieldNames()[0];
        $doctrineTypeField = $this->getDoctrineFieldType($associationFieldIdentifier, $associationResourceClass);

        if ($this->hasValidValues($values, $doctrineTypeField) === false) {
            $this->logger->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(sprintf(
                    'Values for field "%s" are not valid according to the doctrine type.',
                    $field
                )),
            ]);

            return;
        }

        $associationAlias = $alias;
        $associationField = $field;
        if (
            $metadata->isCollectionValuedAssociation($associationField)
            || $metadata->isAssociationInverseSide($field)
        ) {
            $associationAlias = QueryBuilderHelper::addJoinOnce(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $associationField
            );
            $associationField = $associationFieldIdentifier;
        }

        $this->addWhereByStrategy(
            $strategy,
            $queryBuilder,
            $queryNameGenerator,
            $associationAlias,
            $associationField,
            $values,
            $caseSensitive
        );
    }

    /**
     * Gets the ID from an IRI or a raw ID.
     */
    protected function getIdFromValue(string $value): int|string
    {
        try {
            $iriConverter = $this->getIriConverter();
            $item = $iriConverter->getResourceFromIri($value, ['fetch_data' => false]);

            return $this->getPropertyAccessor()
                ->getValue($item, 'id');
        } catch (InvalidArgumentException) {
            // Do nothing, return the raw value
        }

        return $value;
    }

    protected function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    /**
     * When the field should be an integer, check that the given value is a valid one.
     *
     * @param mixed[] $values
     */
    protected function hasValidValues(array $values, mixed $type = null): bool
    {
        foreach ($values as $value) {
            if ($value !== null && in_array($type, (array)self::DOCTRINE_INTEGER_TYPE, true)
                && filter_var($value, FILTER_VALIDATE_INT) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize the values array.
     *
     * @param mixed[] $values
     *
     * @return mixed[]|null
     */
    protected function normalizeValues(array $values, string $property): ?array
    {
        foreach ($values as $key => $value) {
            if (is_int($key) === false || (is_string($value) || is_int($value)) === false) {
                unset($values[$key]);
            }
        }

        if (empty($values)) {
            $this->getLogger()
                ->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(sprintf(
                        'At least one value is required, multiple values should be in' .
                        ' "%1$s[]=firstvalue&%1$s[]=secondvalue" format',
                        $property
                    )),
                ]);

            return null;
        }

        return array_values($values);
    }

    private function getType(?string $doctrineType = null): string
    {
        return match ($doctrineType) {
            Types::ARRAY => 'array',
            Types::BIGINT,
            Types::INTEGER,
            Types::SMALLINT => 'int',
            Types::BOOLEAN => 'bool',
            Types::DATE_MUTABLE,
            Types::TIME_MUTABLE,
            Types::DATETIME_MUTABLE,
            Types::DATETIMETZ_MUTABLE,
            Types::DATE_IMMUTABLE,
            Types::TIME_IMMUTABLE,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIMETZ_IMMUTABLE => DateTimeInterface::class,
            Types::FLOAT => 'float',
            default => 'string',
        };
    }
}
