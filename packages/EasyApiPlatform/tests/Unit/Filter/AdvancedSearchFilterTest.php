<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Unit\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyApiPlatform\Filter\AdvancedSearchFilter;
use EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource\Dummy;
use EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource\RelatedDummy;
use EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\Serializer\NameConverter\CustomConverter;
use Prophecy\Argument;
use Prophecy\Prophet;

final class AdvancedSearchFilterTest extends AbstractFilterTestCase
{
    protected static string $alias = 'oo';

    protected string $filterClass = AdvancedSearchFilter::class;

    /**
     * @var class-string
     */
    protected string $resourceClass = Dummy::class;

    /**
     * @see testApply
     */
    public static function provideApplyTestData(): iterable
    {
        $filterFactory = static fn (
            ManagerRegistry $managerRegistry,
            ?array $properties = null,
        ): AdvancedSearchFilter => self::buildAdvancedSearchFilter($managerRegistry, $properties);

        yield 'exact' => [
            [
                'id' => null,
                'name' => null,
            ],
            [
                'name' => 'exact',
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name = :name_p1', self::$alias, Dummy::class),
            ['name_p1' => 'exact'],
            $filterFactory,
        ];

        yield 'multiple strategies (one strategy)' => [
            [
                'id' => null,
                'name[exact]' => [
                    'name' => 'exact',
                ],
            ],
            ['name[exact]' => 'exact'],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name = :name_p1', self::$alias, Dummy::class),
            ['name_p1' => 'exact'],
            $filterFactory,
        ];

        yield 'multiple strategies (two strategies)' => [
            [
                'id' => null,
                'name[start]' => [
                    'name' => 'start',
                ],
                'name[end]' => [
                    'name' => 'end',
                ],
            ],
            [
                'name[start]' => 'start with',
                'name[end]' => 'end with',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE' .
                ' %1$s.name LIKE CONCAT(:name_p1_0, \'%%\') AND %1$s.name LIKE CONCAT(\'%%\', :name_p2_0)',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'start with',
                'name_p2_0' => 'end with',
            ],
            $filterFactory,
        ];

        yield 'exact (case insensitive)' => [
            [
                'id' => null,
                'name' => 'iexact',
            ],
            [
                'name' => 'exact',
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) = LOWER(:name_p1)', self::$alias, Dummy::class),
            ['name_p1' => 'exact'],
            $filterFactory,
        ];

        yield 'exact (case insensitive, with special characters)' => [
            [
                'id' => null,
                'name' => 'iexact',
            ],
            [
                'name' => 'exact (special)',
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) = LOWER(:name_p1)', self::$alias, Dummy::class),
            ['name_p1' => 'exact (special)'],
            $filterFactory,
        ];

        yield 'exact (multiple values)' => [
            [
                'id' => null,
                'name' => 'exact',
            ],
            [
                'name' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name IN(:name_p1)', self::$alias, Dummy::class),
            [
                'name_p1' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            $filterFactory,
        ];

        yield 'multiple strategies (one strategy; multiple values)' => [
            [
                'id' => null,
                'name[exact]' => [
                    'name' => 'exact',
                ],
            ],
            [
                'name' => [
                    'exact' => [
                        'CaSE',
                        'SENSitive',
                    ],
                ],
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name IN(:name_p1)', self::$alias, Dummy::class),
            [
                'name_p1' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            $filterFactory,
        ];

        yield 'exact (multiple values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'iexact',
            ],
            [
                'name' => [
                    'CaSE',
                    'inSENSitive',
                ],
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) IN(:name_p1)', self::$alias, Dummy::class),
            [
                'name_p1' => [
                    'case',
                    'insensitive',
                ],
            ],
            $filterFactory,
        ];

        yield 'invalid property' => [
            [
                'id' => null,
                'name' => null,
            ],
            [
                'someInvalidProperty' => 'exact',
            ],
            \sprintf('SELECT %s FROM %s %1$s', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'invalid values for relations' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy' => null,
                'relatedDummies' => null,
            ],
            [
                'name' => ['foo'],
                'relatedDummy' => ['some-invalid-value'],
                'relatedDummies' => [['some-invalid-value']],
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name = :name_p1', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'partial' => [
            [
                'id' => null,
                'name' => 'partial',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name LIKE CONCAT(\'%%\', :name_p1_0, \'%%\')',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'partial (case insensitive)' => [
            [
                'id' => null,
                'name' => 'ipartial',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_0, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'partial (multiple values)' => [
            [
                'id' => null,
                'name' => 'partial',
            ],
            [
                'name' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name' .
                ' LIKE CONCAT(\'%%\', :name_p1_0, \'%%\') OR %1$s.name LIKE CONCAT(\'%%\', :name_p1_1, \'%%\')',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'CaSE',
                'name_p1_1' => 'SENSitive',
            ],
            $filterFactory,
        ];

        yield 'partial (multiple values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'ipartial',
            ],
            [
                'name' => [
                    'CaSE',
                    'inSENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_0, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_1, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'case',
                'name_p1_1' => 'insensitive',
            ],
            $filterFactory,
        ];

        yield 'partial (multiple almost same values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'ipartial',
            ],
            [
                'name' => [
                    'blue car',
                    'Blue Car',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_0, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_1, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'blue car',
                'name_p1_1' => 'blue car',
            ],
            $filterFactory,
        ];

        yield 'start' => [
            [
                'id' => null,
                'name' => 'start',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name LIKE CONCAT(:name_p1_0, \'%%\')',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'start (case insensitive)' => [
            [
                'id' => null,
                'name' => 'istart',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_0, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'start (multiple values)' => [
            [
                'id' => null,
                'name' => 'start',
            ],
            [
                'name' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE' .
                ' %1$s.name LIKE CONCAT(:name_p1_0, \'%%\') OR %1$s.name LIKE CONCAT(:name_p1_1, \'%%\')',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'CaSE',
                'name_p1_1' => 'SENSitive',
            ],
            $filterFactory,
        ];

        yield 'start (multiple values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'istart',
            ],
            [
                'name' => [
                    'CaSE',
                    'inSENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_0, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_1, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'case',
                'name_p1_1' => 'insensitive',
            ],
            $filterFactory,
        ];

        yield 'end' => [
            [
                'id' => null,
                'name' => 'end',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name LIKE CONCAT(\'%%\', :name_p1_0)',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'end (case insensitive)' => [
            [
                'id' => null,
                'name' => 'iend',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_0))',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'end (multiple values)' => [
            [
                'id' => null,
                'name' => 'end',
            ],
            [
                'name' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name LIKE CONCAT(\'%%\', :name_p1_0)' .
                ' OR %1$s.name LIKE CONCAT(\'%%\', :name_p1_1)',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'CaSE',
                'name_p1_1' => 'SENSitive',
            ],
            $filterFactory,
        ];

        yield 'end (multiple values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'iend',
            ],
            [
                'name' => [
                    'CaSE',
                    'inSENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_0))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%%\', :name_p1_1))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'case',
                'name_p1_1' => 'insensitive',
            ],
            $filterFactory,
        ];

        yield 'word_start' => [
            [
                'id' => null,
                'name' => 'word_start',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.name LIKE CONCAT(:name_p1_0, \'%%\')' .
                ' OR %1$s.name LIKE CONCAT(\'%% \', :name_p1_0, \'%%\')',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'word_start (case insensitive)' => [
            [
                'id' => null,
                'name' => 'iword_start',
            ],
            [
                'name' => 'partial',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_0, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%% \', :name_p1_0, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            ['name_p1_0' => 'partial'],
            $filterFactory,
        ];

        yield 'word_start (multiple values)' => [
            [
                'id' => null,
                'name' => 'word_start',
            ],
            [
                'name' => [
                    'CaSE',
                    'SENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE (%1$s.name LIKE CONCAT(:name_p1_0, \'%%\')' .
                ' OR %1$s.name LIKE CONCAT(\'%% \', :name_p1_0, \'%%\'))' .
                ' OR (%1$s.name LIKE CONCAT(:name_p1_1, \'%%\')' .
                ' OR %1$s.name LIKE CONCAT(\'%% \', :name_p1_1, \'%%\'))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'CaSE',
                'name_p1_1' => 'SENSitive',
            ],
            $filterFactory,
        ];

        yield 'word_start (multiple values; case insensitive)' => [
            [
                'id' => null,
                'name' => 'iword_start',
            ],
            [
                'name' => [
                    'CaSE',
                    'inSENSitive',
                ],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE (LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_0, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%% \', :name_p1_0, \'%%\')))' .
                ' OR (LOWER(%1$s.name) LIKE LOWER(CONCAT(:name_p1_1, \'%%\'))' .
                ' OR LOWER(%1$s.name) LIKE LOWER(CONCAT(\'%% \', :name_p1_1, \'%%\')))',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1_0' => 'case',
                'name_p1_1' => 'insensitive',
            ],
            $filterFactory,
        ];

        yield 'invalid value for relation' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy' => null,
            ],
            [
                'relatedDummy' => 'some-invalid-value',
            ],
            \sprintf('SELECT %s FROM %s %1$s', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'invalid IRI for relation' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy' => null,
            ],
            [
                'relatedDummy' => '/some-invalid-iri/1',
            ],
            \sprintf('SELECT %s FROM %s %1$s', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'IRI value for relation' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy.id' => null,
            ],
            [
                'relatedDummy.id' => '/related_dummies/1',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s INNER JOIN %1$s.relatedDummy relatedDummy_a1' .
                ' WHERE relatedDummy_a1.id = :id_p1',
                self::$alias,
                Dummy::class
            ),
            ['id_p1' => 1],
            $filterFactory,
        ];

        yield 'mixed IRI and entity ID values for relations' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy' => null,
                'relatedDummies' => null,
            ],
            [
                'relatedDummy' => ['/related_dummies/1', '2'],
                'relatedDummies' => '1',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s INNER JOIN %1$s.relatedDummies relatedDummies_a1' .
                ' WHERE %1$s.relatedDummy IN(:relatedDummy_p1) AND relatedDummies_a1.id = :id_p2',
                self::$alias,
                Dummy::class
            ),
            [
                'relatedDummy_p1' => [1, 2],
                'id_p2' => 1,
            ],
            $filterFactory,
        ];

        yield 'invalid IRI for entityId field' => [
            [
                'entityId' => null,
            ],
            [
                'entityId' => '/some-invalid-iri/1',
            ],
            \sprintf('SELECT %s FROM %s %1$s', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'IRI value for entityId field' => [
            [
                'entityId' => null,
            ],
            [
                'entityId' => '/related_dummies/1',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.entityId = :entityId_p1',
                self::$alias,
                Dummy::class
            ),
            ['entityId_p1' => 1],
            $filterFactory,
        ];

        yield 'mixed IRI and entity ID values for entityId field' => [
            [
                'entityId' => null,
            ],
            [
                'entityId' => ['/related_dummies/1', '2'],
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.entityId IN(:entityId_p1)',
                self::$alias,
                Dummy::class
            ),
            [
                'entityId_p1' => [1, 2],
            ],
            $filterFactory,
        ];

        yield 'nested property' => [
            [
                'id' => null,
                'name' => null,
                'relatedDummy.symfony' => null,
            ],
            [
                'name' => 'exact',
                'relatedDummy.symfony' => 'exact',
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s INNER JOIN %1$s.relatedDummy relatedDummy_a1' .
                ' WHERE %1$s.name = :name_p1 AND relatedDummy_a1.symfony = :symfony_p2',
                self::$alias,
                Dummy::class
            ),
            [
                'name_p1' => 'exact',
                'symfony_p2' => 'exact',
            ],
            $filterFactory,
        ];

        yield 'empty nested property' => [
            [
                'relatedDummy.symfony' => null,
            ],
            [
                'relatedDummy.symfony' => [],
            ],
            \sprintf('SELECT %s FROM %s %1$s', self::$alias, Dummy::class),
            [],
            $filterFactory,
        ];

        yield 'integer value' => [
            [
                'age' => 'exact',
            ],
            [
                'age' => 46,
            ],
            \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.age = :age_p1', self::$alias, RelatedDummy::class),
            ['age_p1' => 46],
            $filterFactory,
            RelatedDummy::class,
        ];

        yield 'related owned one-to-one association' => [
            [
                'id' => null,
                'relatedOwnedDummy' => null,
            ],
            [
                'relatedOwnedDummy' => 1,
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s INNER JOIN %1$s.relatedOwnedDummy relatedOwnedDummy_a1' .
                ' WHERE relatedOwnedDummy_a1.id = :id_p1',
                self::$alias,
                Dummy::class
            ),
            ['id_p1' => 1],
            $filterFactory,
            Dummy::class,
        ];

        yield 'related owning one-to-one association' => [
            [
                'id' => null,
                'relatedOwningDummy' => null,
            ],
            [
                'relatedOwningDummy' => 1,
            ],
            \sprintf(
                'SELECT %s FROM %s %1$s WHERE %1$s.relatedOwningDummy = :relatedOwningDummy_p1',
                self::$alias,
                Dummy::class
            ),
            ['relatedOwningDummy_p1' => 1],
            $filterFactory,
            Dummy::class,
        ];
    }

    public function testApplyWithAnotherAlias(): void
    {
        $filters = ['name' => 'exact'];

        $queryBuilder = $this->repository->createQueryBuilder('somealias');

        $filter = self::buildAdvancedSearchFilter($this->managerRegistry, [
            'id' => null,
            'name' => null,
        ]);
        $filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $this->resourceClass,
            new Get(),
            ['filters' => $filters]
        );

        $expectedDql = \sprintf('SELECT %s FROM %s %1$s WHERE %1$s.name = :name_p1', 'somealias', Dummy::class);
        self::assertEquals($expectedDql, $queryBuilder->getQuery()->getDQL());
    }

    public function testDoubleJoin(): void
    {
        $filters = ['relatedDummy.symfony' => 'foo'];

        $queryBuilder = $this->repository->createQueryBuilder(self::$alias);
        $filter = self::buildAdvancedSearchFilter($this->managerRegistry, ['relatedDummy.symfony' => null]);

        $queryBuilder->innerJoin(\sprintf('%s.relatedDummy', self::$alias), 'relateddummy_a1');
        $filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $this->resourceClass,
            new Get(),
            ['filters' => $filters]
        );

        $actual = \strtolower((string)$queryBuilder->getQuery()->getDQL());
        $expected = \strtolower(\sprintf(
            'SELECT %s FROM %s %1$s inner join %1$s.relatedDummy relateddummy_a1' .
            ' WHERE relateddummy_a1.symfony = :symfony_p1',
            self::$alias,
            Dummy::class
        ));
        self::assertEquals($actual, $expected);
    }

    public function testGetDescription(): void
    {
        $filter = self::buildAdvancedSearchFilter($this->managerRegistry, [
            'id' => null,
            'name' => null,
            'name_exact' => [
                'name' => 'exact',
            ],
            'name_partial' => [
                'name' => 'partial',
            ],
            'name[istart]' => [
                'name' => 'istart',
            ],
            'name[exact]' => [
                'name' => 'exact',
            ],
            'alias' => null,
            'dummy' => null,
            'dummyDate' => null,
            'jsonData' => null,
            'arrayData' => null,
            'nameConverted' => null,
            'foo' => null,
            'relatedDummies.dummyDate' => null,
            'relatedDummies.name[start]' => [
                'relatedDummies.name' => 'start',
            ],
            'relatedDummy' => null,
        ]);

        self::assertEquals([
            'id' => [
                'property' => 'id',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'id[]' => [
                'property' => 'id',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name[]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name_exact' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name_exact[]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name_partial' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'partial',
                'is_collection' => false,
            ],
            'name[istart]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'istart',
                'is_collection' => false,
            ],
            'name[exact]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name[exact][]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'alias' => [
                'property' => 'alias',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'alias[]' => [
                'property' => 'alias',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummy' => [
                'property' => 'dummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummy[]' => [
                'property' => 'dummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummyDate' => [
                'property' => 'dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummyDate[]' => [
                'property' => 'dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'jsonData' => [
                'property' => 'jsonData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'jsonData[]' => [
                'property' => 'jsonData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'arrayData' => [
                'property' => 'arrayData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'arrayData[]' => [
                'property' => 'arrayData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name_converted' => [
                'property' => 'name_converted',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name_converted[]' => [
                'property' => 'name_converted',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'relatedDummies.dummyDate' => [
                'property' => 'relatedDummies.dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'relatedDummies.dummyDate[]' => [
                'property' => 'relatedDummies.dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'relatedDummies.name[start]' => [
                'property' => 'relatedDummies.name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'start',
                'is_collection' => false,
            ],
            'relatedDummy' => [
                'property' => 'relatedDummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'relatedDummy[]' => [
                'property' => 'relatedDummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
        ], $filter->getDescription($this->resourceClass));
    }

    public function testGetDescriptionDefaultFields(): void
    {
        $filter = self::buildAdvancedSearchFilter($this->managerRegistry);

        self::assertEquals([
            'id' => [
                'property' => 'id',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'id[]' => [
                'property' => 'id',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name[]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'alias' => [
                'property' => 'alias',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'alias[]' => [
                'property' => 'alias',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'description' => [
                'property' => 'description',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'description[]' => [
                'property' => 'description',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummy' => [
                'property' => 'dummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummy[]' => [
                'property' => 'dummy',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummyDate' => [
                'property' => 'dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummyDate[]' => [
                'property' => 'dummyDate',
                'type' => 'DateTimeInterface',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummyFloat' => [
                'property' => 'dummyFloat',
                'type' => 'float',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummyFloat[]' => [
                'property' => 'dummyFloat',
                'type' => 'float',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummyPrice' => [
                'property' => 'dummyPrice',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummyPrice[]' => [
                'property' => 'dummyPrice',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'entityId' => [
                'property' => 'entityId',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'entityId[]' => [
                'property' => 'entityId',
                'type' => 'int',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'jsonData' => [
                'property' => 'jsonData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'jsonData[]' => [
                'property' => 'jsonData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'arrayData' => [
                'property' => 'arrayData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'arrayData[]' => [
                'property' => 'arrayData',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'name_converted' => [
                'property' => 'name_converted',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'name_converted[]' => [
                'property' => 'name_converted',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
            'dummyBoolean' => [
                'property' => 'dummyBoolean',
                'type' => 'bool',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'dummyBoolean[]' => [
                'property' => 'dummyBoolean',
                'type' => 'bool',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => true,
            ],
        ], $filter->getDescription($this->resourceClass));
    }

    public function testJoinLeft(): void
    {
        $filters = [
            'relatedDummy.symfony' => 'foo',
            'relatedDummy.thirdLevel.level' => '3',
        ];

        $queryBuilder = $this->repository->createQueryBuilder(self::$alias);
        $queryBuilder->leftJoin(\sprintf('%s.relatedDummy', self::$alias), 'relateddummy_a1');

        $filter = self::buildAdvancedSearchFilter(
            $this->managerRegistry,
            [
                'relatedDummy.symfony' => null,
                'relatedDummy.thirdLevel.level' => null,
            ]
        );
        $filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $this->resourceClass,
            new Get(),
            ['filters' => $filters]
        );

        $actual = \strtolower((string)$queryBuilder->getQuery()->getDQL());
        $expected = \strtolower(\sprintf(
            'SELECT %s FROM %s %1$s LEFT JOIN %1$s.relatedDummy relateddummy_a1' .
            ' LEFT JOIN relateddummy_a1.thirdLevel thirdLevel_a1' .
            ' WHERE relateddummy_a1.symfony = :symfony_p1 AND thirdLevel_a1.level = :level_p2',
            self::$alias,
            Dummy::class
        ));
        self::assertEquals($actual, $expected);
    }

    public function testTripleJoin(): void
    {
        $filters = [
            'relatedDummy.symfony' => 'foo',
            'relatedDummy.thirdLevel.level' => '2',
        ];

        $queryBuilder = $this->repository->createQueryBuilder(self::$alias);
        $filter = self::buildAdvancedSearchFilter(
            $this->managerRegistry,
            [
                'relatedDummy.symfony' => null,
                'relatedDummy.thirdLevel.level' => null,
            ]
        );

        $queryBuilder->innerJoin(\sprintf('%s.relatedDummy', self::$alias), 'relateddummy_a1');
        $queryBuilder->innerJoin('relateddummy_a1.thirdLevel', 'thirdLevel_a1');

        $filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $this->resourceClass,
            new Get(),
            ['filters' => $filters]
        );
        $actual = \strtolower((string)$queryBuilder->getQuery()->getDQL());
        $expected = \strtolower(\sprintf(
            'SELECT %s FROM %s %1$s INNER JOIN %1$s.relatedDummy relateddummy_a1' .
            ' INNER JOIN relateddummy_a1.thirdLevel thirdLevel_a1' .
            ' WHERE relateddummy_a1.symfony = :symfony_p1 AND thirdLevel_a1.level = :level_p2',
            self::$alias,
            Dummy::class
        ));
        self::assertEquals($actual, $expected);
    }

    protected static function buildAdvancedSearchFilter(
        ManagerRegistry $managerRegistry,
        ?array $properties = null,
    ): AdvancedSearchFilter {
        $prophet = new Prophet();
        $relatedDummyProphecy = $prophet->prophesize(RelatedDummy::class);
        $iriConverterProphecy = $prophet->prophesize(IriConverterInterface::class);

        $iriConverterProphecy->getResourceFromIri(Argument::type('string'), ['fetch_data' => false])
            ->will(function ($args) use ($relatedDummyProphecy): RelatedDummy {
                if (\str_contains((string)$args[0], '/related_dummies')) {
                    $relatedDummyProphecy->getId()
                        ->shouldBeCalled()
                        ->willReturn(1);

                    return $relatedDummyProphecy->reveal();
                }

                throw new InvalidArgumentException();
            });

        /** @var \ApiPlatform\Metadata\IriConverterInterface $iriConverter */
        $iriConverter = $iriConverterProphecy->reveal();
        $propertyAccessor = self::$kernel->getContainer()->get('test.property_accessor');

        return new AdvancedSearchFilter(
            $managerRegistry,
            $iriConverter,
            $propertyAccessor,
            null,
            $properties,
            null,
            new CustomConverter(),
            ['entityId']
        );
    }
}
