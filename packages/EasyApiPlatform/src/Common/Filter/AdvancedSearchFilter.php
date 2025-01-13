<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\Filter;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Closure;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyApiPlatform\Common\IriConverter\IriConverterTrait;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

/**
 * Filter the collection by given properties.
 * This filter allows to define multiple search strategy for the same property on a single resource.
 *
 * This class was created by copying of base ApiPlatform SearchFilter with the following changes:
 *   - Allow to configure multiple strategies for the same property.
 *
 * @see \ApiPlatform\Doctrine\Orm\Filter\SearchFilter
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
    use IriConverterTrait;
    use SearchFilterTrait;

    public const DOCTRINE_INTEGER_TYPE = Types::INTEGER;

    private const DOCTRINE_UUID_TYPE = 'uuid';

    private const NIL_UUID = '00000000-0000-0000-0000-000000000000';

    /**
     * @param string[] $iriFields
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        IriConverterInterface $iriConverter,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?IdentifiersExtractorInterface $identifiersExtractor = null,
        ?NameConverterInterface $nameConverter = null,
        private readonly array $iriFields = [],
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);

        $this->iriConverter = $iriConverter;
        $this->identifiersExtractor = $identifiersExtractor;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();
        if ($properties === null) {
            $properties = \array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $strategy) {
            $filterParameter = $property;

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

            $propertyName = $this->normalizePropertyName($property);
            $filterParameterName = $this->normalizePropertyName($filterParameter);
            if ($metadata->hasField($field)) {
                $typeOfField = $this->getType($metadata->getTypeOfField($field));
                $strategy ??= self::STRATEGY_EXACT;
                $filterParameterNames = [$filterParameterName];

                if ($strategy === self::STRATEGY_EXACT) {
                    $filterParameterNames[] = $filterParameterName . '[]';
                }

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'is_collection' => \str_ends_with($filterParameterName, '[]'),
                        'property' => $propertyName,
                        'required' => false,
                        'strategy' => $strategy,
                        'type' => $typeOfField,
                    ];
                }
            } elseif ($metadata->hasAssociation($field)) {
                $filterParameterNames = [
                    $filterParameterName,
                    $filterParameterName . '[]',
                ];

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'is_collection' => \str_ends_with($filterParameterName, '[]'),
                        'property' => $propertyName,
                        'required' => false,
                        'strategy' => self::STRATEGY_EXACT,
                        'type' => 'string',
                    ];
                }
            }
        }

        return $description;
    }

    /**
     * Adds where clause according to the strategy.
     *
     * @throws \ApiPlatform\Metadata\Exception\InvalidArgumentException If strategy does not exist
     */
    protected function addWhereByStrategy(
        string $strategy,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $field,
        mixed $values,
        bool $caseSensitive,
    ): void {
        if (\is_array($values) === false) {
            $values = [$values];
        }

        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = ':' . $queryNameGenerator->generateParameterName($field);
        $aliasedField = \sprintf('%s.%s', $alias, $field);

        if ($strategy === '' || $strategy === self::STRATEGY_EXACT) {
            if (\count($values) === 1) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($wrapCase($aliasedField), $wrapCase($valueParameter)))
                    ->setParameter($valueParameter, $values[0]);

                return;
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in($wrapCase($aliasedField), $valueParameter))
                ->setParameter($valueParameter, $caseSensitive ? $values : \array_map('strtolower', $values));

            return;
        }

        $ors = [];
        $parameters = [];
        foreach ($values as $key => $value) {
            $keyValueParameter = \sprintf('%s_%s', $valueParameter, $key);
            $parameters[] = [$caseSensitive ? $value : \strtolower((string)$value), $keyValueParameter];

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
                default => throw new InvalidArgumentException(\sprintf('strategy %s does not exist.', $strategy)),
            };
        }

        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$ors));
        foreach ($parameters as $parameter) {
            $queryBuilder->setParameter($parameter[1], $parameter[0]);
        }
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

            return \sprintf('LOWER(%s)', $expr);
        };
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $filterParameter = $property;
        if (isset($this->properties[$filterParameter]) && \is_array($this->properties[$filterParameter])) {
            $property = (string)\array_keys($this->properties[$filterParameter])[0];
            $strategy = \array_values($this->properties[$filterParameter])[0];
        }

        if (\is_array($value) && \count($value) > 0) {
            $valueKey = \array_keys($value)[0];
            $possibleFilterParameter = $filterParameter . '[' . $valueKey . ']';

            if (
                isset($this->properties[$possibleFilterParameter])
                && \is_array($this->properties[$possibleFilterParameter])
            ) {
                $filterParameter = $possibleFilterParameter;

                $property = (string)\array_keys($this->properties[$filterParameter])[0];
                $strategy = \array_values($this->properties[$filterParameter])[0];

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
        $strategy ??= $this->properties[$filterParameter] ?? self::STRATEGY_EXACT;

        // Prefixing the strategy with i makes it case-insensitive
        if (\str_starts_with((string)$strategy, 'i')) {
            $strategy = \substr((string)$strategy, 1);
            $caseSensitive = false;
        }

        $metadata = $this->getNestedMetadata($resourceClass, $associations);

        if ($metadata->hasField($field)) {
            if ($field === 'id' || \in_array($field, $this->iriFields, true)) {
                $values = \array_map($this->getIdFromValue(...), $values);
            }

            if ($this->hasValidValues($values, $this->getDoctrineFieldType($property, $resourceClass)) === false) {
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(\sprintf(
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

        // Metadata doesn't have the field, nor an association on the field
        if ($metadata->hasAssociation($field) === false) {
            return;
        }

        // Association, let's fetch the entity (or reference to it) if we can so we can make sure we get its orm id
        /** @var string $associationResourceClass */
        $associationResourceClass = $metadata->getAssociationTargetClass($field);
        $associationMetadata = $this->getClassMetadata($associationResourceClass);
        $associationFieldIdentifier = $associationMetadata->getIdentifierFieldNames()[0];
        $doctrineTypeField = $this->getDoctrineFieldType($associationFieldIdentifier, $associationResourceClass);

        $values = \array_map(function ($value) use ($associationFieldIdentifier, $doctrineTypeField) {
            if (\is_numeric($value)) {
                return $value;
            }

            if ($this->isValidUuid($value)) {
                return $value;
            }

            try {
                $item = $this->getIriConverter()
                    ->getResourceFromIri($value, ['fetch_data' => false]);

                return $this->propertyAccessor->getValue($item, $associationFieldIdentifier);
            } catch (InvalidArgumentException) {
                /*
                 * Can we do better? This is not the ApiResource the call was made on,
                 * so we don't get any kind of api metadata for it without (a lot of?) work elsewhere...
                 * Let's just pretend it's always the ORM id for now.
                 */
                if ($this->hasValidValues([$value], $doctrineTypeField) === false) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(\sprintf(
                            'Values for field "%s" are not valid according to the doctrine type.',
                            $associationFieldIdentifier
                        )),
                    ]);

                    return self::NIL_UUID;
                }

                return $value;
            }
        }, $values);

        $expected = \count($values);
        $values = \array_filter($values, static fn ($value): bool => $value !== null);
        if ($expected > \count($values)) {
            /*
             * Shouldn't this actually fail harder?
             */
            $this->logger->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(\sprintf(
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

    protected function getIdFromValue(string $value): mixed
    {
        if (\is_numeric($value)) {
            return $value;
        }

        if ($this->isValidUuid($value)) {
            return $value;
        }

        try {
            $iriConverter = $this->getIriConverter();
            $item = $iriConverter->getResourceFromIri($value, ['fetch_data' => false]);

            if ($this->identifiersExtractor === null) {
                return $this->getPropertyAccessor()
                    ->getValue($item, 'id');
            }

            $identifiers = $this->identifiersExtractor->getIdentifiersFromItem($item);

            return \count($identifiers) === 1 ? \array_pop($identifiers) : $identifiers;
        } catch (InvalidArgumentException) {
            return self::NIL_UUID;
        }
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    protected function hasValidValues(array $values, ?string $type = null): bool
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            if (
                \in_array($type, (array)self::DOCTRINE_INTEGER_TYPE, true)
                && \filter_var($value, \FILTER_VALIDATE_INT) === false
            ) {
                return false;
            }

            if ($type === self::DOCTRINE_UUID_TYPE && $this->isValidUuid($value) === false) {
                return false;
            }
        }

        return true;
    }

    protected function isValidUuid(mixed $value): bool
    {
        if (\is_string($value) === false) {
            return false;
        }

        if (\class_exists(SymfonyUuid::class)) {
            return SymfonyUuid::isValid($value);
        }

        if (\class_exists(RamseyUuid::class)) {
            return RamseyUuid::isValid($value);
        }

        return \preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    private function getType(?string $doctrineType = null): string
    {
        return match ($doctrineType) {
            Types::BIGINT,
            Types::INTEGER,
            Types::SMALLINT => 'int',
            Types::BOOLEAN => 'bool',
            Types::DATETIMETZ_IMMUTABLE,
            Types::DATETIMETZ_MUTABLE,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIME_MUTABLE,
            Types::DATE_IMMUTABLE,
            Types::DATE_MUTABLE,
            Types::TIME_IMMUTABLE,
            Types::TIME_MUTABLE => DateTimeInterface::class,
            Types::FLOAT => 'float',
            default => 'string',
        };
    }
}
