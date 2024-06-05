<?php
declare(strict_types=1);

namespace EonX\EasyTest\Traits;

use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

trait DatabaseEntityTrait
{
    /**
     * @param class-string<object> $entityClass
     */
    protected static function assertEntityCount(string $entityClass, int $expectedCount, ?array $criteria = null): void
    {
        $actualCount = self::getEntityManager()->getRepository($entityClass)->count($criteria ?? []);

        if ($expectedCount !== $actualCount) {
            $message = self::createMessageForEntityAssertion(
                \sprintf(
                    'Expected %d, but %d %s entities were found in the database.',
                    $expectedCount,
                    $actualCount,
                    $entityClass
                ),
                $entityClass,
                $criteria ?? []
            );

            self::fail($message);
        }

        self::assertTrue(true);
    }

    /**
     * @param class-string<object> $entityClass
     */
    protected static function assertEntityDoesNotExist(
        string $entityClass,
        ?array $criteria = null,
        ?string $message = null,
    ): void {
        $entity = self::findOneEntity($entityClass, $criteria ?? []);

        if ($entity !== null) {
            $message = self::createMessageForEntityAssertion(
                $message ?? \sprintf("Failed asserting that the %s doesn't exist in the database.", $entityClass),
                $entityClass,
                $criteria ?? []
            );

            self::fail($message);
        }

        self::assertTrue(true);
    }

    /**
     * @param class-string<object> $entityClass
     */
    protected static function assertEntityExists(
        string $entityClass,
        array $criteria,
        ?array $jsonAttributes = null,
        ?string $message = null,
    ): void {
        $entity = self::findOneEntity($entityClass, $criteria, $jsonAttributes);

        if ($entity === null) {
            $message = self::createMessageForEntityAssertion(
                $message ?? \sprintf('Failed asserting that the %s exists in the database.', $entityClass),
                $entityClass,
                $criteria
            );

            self::fail($message);
        }

        self::assertTrue(true);
    }

    /**
     * @template TEntity of object
     *
     * @param class-string<TEntity> $entityClass
     *
     * @return TEntity|null
     */
    protected static function findOneEntity(
        string $entityClass,
        array $criteria,
        ?array $jsonAttributes = null,
    ): ?object {
        $alias = 'entity';
        $queryBuilder = self::getEntityManager()->createQueryBuilder();

        $queryBuilder->select($alias)
            ->from($entityClass, $alias);

        foreach ($criteria as $criteriaProperty => $criteriaValue) {
            if (\in_array($criteriaProperty, $jsonAttributes ?? [], true)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()
                        ->eq(
                            \sprintf('CONTAINS(%s.%s, :%s)', $alias, $criteriaProperty, $criteriaProperty),
                            'TRUE'
                        )
                );
                $queryBuilder->setParameter($criteriaProperty, \json_encode($criteriaValue));

                continue;
            }
            if ($criteriaValue === null) {
                $queryBuilder->andWhere(\sprintf('%s.%s IS NULL', $alias, $criteriaProperty));

                continue;
            }
            $paramName = \str_replace('.', '', $criteriaProperty);
            $queryBuilder->andWhere(
                $queryBuilder->expr()
                    ->eq(
                        \sprintf('%s.%s', $alias, $criteriaProperty),
                        \sprintf(':%s', $paramName)
                    )
            );
            $queryBuilder->setParameter($paramName, $criteriaValue);
        }

        /**
         * @var TEntity|null $entity
         */
        $entity = $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $entity;
    }

    protected static function normalizeEntity(object $entity, ?array $attrsToNormalize = null): array
    {
        $serializer = new Serializer([
            new DateTimeNormalizer(),
            new UidNormalizer(),
            new ObjectNormalizer(),
        ]);

        if (\is_array($attrsToNormalize)) {
            $attrsToNormalize = self::clearAsterisksFromAttrs($attrsToNormalize);
        }

        return (array)$serializer->normalize($entity, null, ['attributes' => $attrsToNormalize]);
    }

    private static function clearAsterisksFromAttrs(array $attrs): array
    {
        foreach ($attrs as $key => $value) {
            if (\is_array($value)) {
                $value = self::clearAsterisksFromAttrs($value);
            }
            if ($key === '*') {
                unset($attrs[$key]);
                $attrs[] = $value;
            }
        }

        return $attrs;
    }

    /**
     * @param class-string<object> $entityClass
     */
    private static function createMessageForEntityAssertion(
        string $message,
        string $entityClass,
        array $criteria,
    ): string {
        $cloner = new VarCloner(
            \array_merge(
                AbstractCloner::$defaultCasters,
                [
                    DateTimeInterface::class => static fn (DateTimeInterface $dateTime): array => [
                        'date' => $dateTime->format(DateTimeInterface::ATOM),
                    ],
                    Uuid::class => static fn (Uuid $uid): array => ['uid' => (string)$uid],
                ]
            )
        );
        $dumper = new CliDumper(null, null, AbstractDumper::DUMP_LIGHT_ARRAY);

        $message .= \sprintf(
            "\n\nQuery params:\n%s",
            \count($criteria) > 0 ? $dumper->dump($cloner->cloneVar($criteria), true) : 'none'
        );

        $entities = self::getEntityManager()->getRepository($entityClass)->findAll();

        if (\count($entities) === 0) {
            $message .= \sprintf("\n\nNo %s entities found in the database.", $entityClass);

            return $message;
        }

        $fields = \array_keys($criteria);
        \natsort($fields);
        $cloner->addCasters([
            $entityClass => static function (
                object $entity,
                array $array,
                Stub $stub,
                bool $isNested,
            ) use ($fields): array {
                if ($isNested) {
                    return $array;
                }

                if (\count($fields) === 0) {
                    return $array;
                }

                $result = [];
                foreach ($fields as $field) {
                    foreach ($array as $key => $value) {
                        if (\str_ends_with($key, $field)) {
                            $result[$key] = $value;

                            continue 2;
                        }
                    }
                }

                return $result;
            },
        ]);

        $message .= \sprintf(
            "\n\nThe following %s entities were found:\n\n%s",
            $entityClass,
            \implode(
                "\n\n",
                \array_map(
                    static fn ($entity, $index): string => \sprintf(
                        "#Entity %s\n%s",
                        $index,
                        $dumper->dump($cloner->cloneVar($entity), true)
                    ),
                    $entities,
                    \array_keys($entities)
                )
            )
        );

        return $message;
    }
}
