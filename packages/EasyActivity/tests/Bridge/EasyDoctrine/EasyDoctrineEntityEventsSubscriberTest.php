<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\EasyDoctrine;

use Carbon\Carbon;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;

final class EasyDoctrineEntityEventsSubscriberTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testPropertyFilters
     */
    public function providerProperties(): iterable
    {
        yield 'only allowed properties' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => ['title', 'content', 234],
            'disallowedProperties' => null,
            'expectedDataProperties' => ['title', 'content'],
        ];

        yield 'allowed and disallowed properties intersection' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => ['title', 'content'],
            'disallowedProperties' => ['content'],
            'expectedDataProperties' => ['title'],
        ];

        yield 'only disallowed properties' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => null,
            'disallowedProperties' => ['createdAt'],
            'expectedDataProperties' => ['title', 'author', 'content'],
        ];

        yield 'all properties are disallowed' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => null,
            'disallowedProperties' => ['title', 'createdAt', 'author', 'content'],
            'expectedDataProperties' => null,
        ];

        yield 'disallowed properties and defined on global and entity levels' => [
            'globalDisallowedProperties' => ['createdAt'],
            'allowedProperties' => null,
            'disallowedProperties' => ['title', 'author'],
            'expectedDataProperties' => ['content'],
        ];
    }

    public function testLoggerDoesNothingWhenSubjectIsNotDefinedInConfig(): void
    {
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [],
            ]
        );
        $article = new Article();
        $article->setTitle('Resolver');
        $article->setContent('Test actor resolver');

        $entityManager->persist($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(0, $logEntries);
    }

    public function testLoggerSucceedsForUpdatedSubjects(): void
    {
        Carbon::setTestNow('2021-10-10 10:00:00');
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [
                        'type' => 'article',
                        'allowed_properties' => ['title', 'content'],
                    ],
                ],
            ]
        );

        $article = new Article();
        $article->setTitle('Title 1');
        $article->setContent('Content');

        $entityManager->persist($article);
        $entityManager->flush();

        $article->setTitle('Title 2');
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);

        self::assertCount(2, $logEntries);
        self::assertEquals([
            'actor_type' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
            'actor_id' => null,
            'actor_name' => null,
            'action' => ActivityLogEntry::ACTION_CREATE,
            'subject_type' => 'article',
            'subject_id' => '1',
            'data' => \json_encode([
                'content' => 'Content',
                'title' => 'Title 1',
            ]),
            'created_at' => '2021-10-10 10:00:00',
            'updated_at' => '2021-10-10 10:00:00',
            'old_data' => null,
        ], $logEntries[0]);
        self::assertEquals([
            'actor_type' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
            'actor_id' => null,
            'actor_name' => null,
            'action' => ActivityLogEntry::ACTION_UPDATE,
            'subject_type' => 'article',
            'subject_id' => '1',
            'data' => \json_encode([
                'title' => 'Title 2',
            ]),
            'old_data' => \json_encode([
                'title' => 'Title 1',
            ]),
            'created_at' => '2021-10-10 10:00:00',
            'updated_at' => '2021-10-10 10:00:00',
        ], $logEntries[1]);
    }

    public function testLoggerSucceedsWithCollections(): void
    {
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [
                        'allowed_properties' => [
                            'title',
                            'comments',
                        ],
                    ],
                ],
            ],
            null,
            null,
            [Article::class, Comment::class]
        );

        $article = new Article();
        $article->setTitle('Test collections');
        $article->setContent('Content');
        $article->addComment((new Comment())->setMessage('comment 1'));
        $article->addComment((new Comment())->setMessage('comment 2'));

        $entityManager->persist($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(1, $logEntries);
        self::assertSame(
            ['title' => 'Test collections'],
            \json_decode($logEntries[0]['data'], true)
        );
    }

    public function testLoggerSucceedsWithCustomActorResolver(): void
    {
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [],
                ],
            ],
            new class() implements ActorResolverInterface {
                public function resolveActor(object $object): ActorInterface
                {
                    return new Actor('actor-type', 'actor-id', 'actor-name');
                }
            }
        );

        $article = new Article();
        $article->setTitle('Resolver');
        $article->setContent('Test actor resolver');

        $entityManager->persist($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(1, $logEntries);
        self::assertSame('actor-id', $logEntries[0]['actor_id']);
        self::assertSame('actor-type', $logEntries[0]['actor_type']);
        self::assertSame('actor-name', $logEntries[0]['actor_name']);
    }

    public function testLoggerSucceedsWithRelatedObjects(): void
    {
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [
                        'allowed_properties' => [
                            'title',
                            'author',
                        ],
                    ],
                    Author::class => [],
                ],
            ]
        );

        $author = new Author();
        $author->setName('John');
        $author->setPosition(1);
        $article = new Article();
        $article->setTitle('Resolver');
        $article->setContent('Test actor resolver');
        $article->setAuthor($author);

        $entityManager->persist($author);
        $entityManager->persist($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(2, $logEntries);
        self::assertSame(
            [
                'name' => 'John',
                'position' => 1,
            ],
            \json_decode($logEntries[0]['data'], true)
        );
        self::assertSame(
            [
                'title' => 'Resolver',
                'author' => ['id' => 1],
            ],
            \json_decode($logEntries[1]['data'], true)
        );
    }

    /**
     * @param string[] $globalDisallowedProperties
     * @param string[] $allowedProperties
     * @param string[] $disallowedProperties
     * @param string[] $expectedDataProperties
     *
     * @dataProvider providerProperties
     */
    public function testPropertyFilters(
        ?array $globalDisallowedProperties = null,
        ?array $allowedProperties = null,
        ?array $disallowedProperties = null,
        ?array $expectedDataProperties = null
    ): void {
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [
                        'allowed_properties' => $allowedProperties,
                        'disallowed_properties' => $disallowedProperties,
                    ],
                ],
                'disallowed_properties' => $globalDisallowedProperties,
            ]
        );

        $article = new Article();
        $article->setTitle('Title');
        $article->setContent('Content');

        $entityManager->persist($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        if ($expectedDataProperties === null) {
            self::assertCount(0, $logEntries);

            return;
        }
        self::assertCount(1, $logEntries);
        self::assertEqualsCanonicalizing(
            $expectedDataProperties,
            \array_keys(\json_decode($logEntries[0]['data'], true))
        );
    }
}
