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
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyDoctrineEntityEventsSubscriberTest extends AbstractSymfonyTestCase
{
    /**
     * @see testPropertyFilters
     */
    public static function provideProperties(): iterable
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
            'allowedProperties' => [],
            'disallowedProperties' => ['createdAt'],
            'expectedDataProperties' => ['title', 'author', 'id', 'content'],
        ];

        yield 'all properties are disallowed' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => [],
            'disallowedProperties' => ['title', 'createdAt', 'author', 'content', 'id'],
            'expectedDataProperties' => null,
        ];

        yield 'allowed properties is explicitly set as null' => [
            'globalDisallowedProperties' => null,
            'allowedProperties' => null,
            'disallowedProperties' => ['title', 'createdAt', 'author', 'content'],
            'expectedDataProperties' => null,
        ];

        yield 'disallowed properties and defined on global and entity levels' => [
            'globalDisallowedProperties' => ['createdAt'],
            'allowedProperties' => [],
            'disallowedProperties' => ['title', 'author'],
            'expectedDataProperties' => ['content', 'id'],
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

    public function testLoggerSucceedsForDeletedSubjects(): void
    {
        Carbon::setTestNow('2021-10-10 10:00:00.001001');
        $entityManager = EntityManagerStub::createFromEasyActivityConfig(
            [
                'subjects' => [
                    Article::class => [
                        'type' => 'article',
                    ],
                ],
            ]
        );
        $author = new Author();
        $author->setPosition(1);
        $author->setName('John');
        $entityManager->persist($author);
        $article = new Article();
        $article->setTitle('Title 1');
        $article->setContent('Content');
        $article->setAuthor($author);
        $entityManager->persist($article);
        $entityManager->flush();
        $articleId = $article->getId();
        $authorId = $author->getId();

        $entityManager->remove($article);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(2, $logEntries);
        self::assertEquals([
            'actor_type' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
            'actor_id' => null,
            'actor_name' => null,
            'action' => ActivityLogEntry::ACTION_DELETE,
            'subject_type' => 'article',
            'subject_id' => $articleId,
            'subject_data' => null,
            'subject_old_data' => \json_encode([
                'content' => 'Content',
                'createdAt' => '2021-10-10T10:00:00+00:00',
                'id' => $articleId,
                'title' => 'Title 1',
                'author' => [
                    'id' => $authorId,
                ],
                'comments' => [],

            ]),
            'created_at' => '2021-10-10 10:00:00.001001',
            'updated_at' => '2021-10-10 10:00:00.001001',
        ], $logEntries[1]);
    }

    public function testLoggerSucceedsForSubjectsCreatedInTransaction(): void
    {
        Carbon::setTestNow('2021-10-10 10:00:00.899933');
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
        $article->setContent('Content 1');

        $entityManager->persist($article);
        $entityManager->wrapInTransaction(function () use ($entityManager, $article): void {
            $article->setTitle('Title 2');

            $entityManager->flush();

            $article->setTitle('Title 3');

            $entityManager->flush();

            $article->setContent('Content 2');
        });

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(1, $logEntries);
        self::assertEquals([
            'actor_type' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
            'actor_id' => null,
            'actor_name' => null,
            'action' => ActivityLogEntry::ACTION_CREATE,
            'subject_type' => 'article',
            'subject_id' => $article->getId(),
            'subject_data' => \json_encode([
                'content' => 'Content 2',
                'title' => 'Title 3',
            ]),
            'subject_old_data' => null,
            'created_at' => '2021-10-10 10:00:00.899933',
            'updated_at' => '2021-10-10 10:00:00.899933',
        ], $logEntries[0]);
    }

    public function testLoggerSucceedsForUpdatedSubjects(): void
    {
        Carbon::setTestNow('2021-10-10 10:00:00.899933');
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
            'subject_id' => $article->getId(),
            'subject_data' => \json_encode([
                'content' => 'Content',
                'title' => 'Title 1',
            ]),
            'subject_old_data' => null,
            'created_at' => '2021-10-10 10:00:00.899933',
            'updated_at' => '2021-10-10 10:00:00.899933',
        ], $logEntries[0]);
        self::assertEquals([
            'actor_type' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
            'actor_id' => null,
            'actor_name' => null,
            'action' => ActivityLogEntry::ACTION_UPDATE,
            'subject_type' => 'article',
            'subject_id' => $article->getId(),
            'subject_data' => \json_encode([
                'title' => 'Title 2',
            ]),
            'subject_old_data' => \json_encode([
                'title' => 'Title 1',
            ]),
            'created_at' => '2021-10-10 10:00:00.899933',
            'updated_at' => '2021-10-10 10:00:00.899933',
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
        $commentA = (new Comment())->setMessage('comment 1');
        $commentB = (new Comment())->setMessage('comment 2');
        $commentC = (new Comment())->setMessage('comment 3');
        $commentD = (new Comment())->setMessage('comment 4');
        $article->addComment($commentA);
        $article->addComment($commentB);
        $article->addComment($commentC);
        $article->addComment($commentD);

        $entityManager->persist($article);
        $entityManager->flush();
        $article->getComments()
            ->removeElement($commentC);
        $entityManager->flush();
        $commentDId = $commentD->getId();
        $entityManager->remove($commentD);
        $entityManager->flush();
        $commentA->setMessage('comment 1 updated');
        $entityManager->flush();
        $commentE = (new Comment())->setMessage('comment 5');
        $article->addComment($commentE);
        $entityManager->flush();

        $logEntries = $this->getLogEntries($entityManager);
        self::assertCount(4, $logEntries);
        // Create an article
        self::assertSame('create', $logEntries[0]['action']);
        self::assertSame(
            [
                'title' => 'Test collections',
                'comments' => [$commentA->getId(), $commentB->getId(), $commentC->getId(), $commentDId],
            ],
            \json_decode((string)$logEntries[0]['subject_data'], true)
        );
        // Remove the comment C from collection
        self::assertSame('update', $logEntries[1]['action']);
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId(), $commentDId],
            ],
            \json_decode((string)$logEntries[1]['subject_data'], true)
        );
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId(), $commentC->getId(), $commentDId],
            ],
            \json_decode((string)$logEntries[1]['subject_old_data'], true)
        );
        // Remove the comment D entity
        self::assertSame('update', $logEntries[2]['action']);
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId()],
            ],
            \json_decode((string)$logEntries[2]['subject_data'], true)
        );
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId(), $commentDId],
            ],
            \json_decode((string)$logEntries[2]['subject_old_data'], true)
        );
        // Add a new comment E to the collection
        self::assertSame('update', $logEntries[3]['action']);
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId(), $commentE->getId()],
            ],
            \json_decode((string)$logEntries[3]['subject_data'], true)
        );
        self::assertSame(
            [
                'comments' => [$commentA->getId(), $commentB->getId()],
            ],
            \json_decode((string)$logEntries[3]['subject_old_data'], true)
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
                public function resolve(object $object): ActorInterface
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
                'id' => $author->getId(),
                'name' => 'John',
                'position' => 1,
            ],
            \json_decode((string)$logEntries[0]['subject_data'], true)
        );
        self::assertSame(
            [
                'title' => 'Resolver',
                'author' => ['id' => $author->getId()],
            ],
            \json_decode((string)$logEntries[1]['subject_data'], true)
        );
    }

    /**
     * @param string[] $globalDisallowedProperties
     * @param string[] $allowedProperties
     * @param string[] $disallowedProperties
     * @param string[] $expectedDataProperties
     */
    #[DataProvider('provideProperties')]
    public function testPropertyFilters(
        ?array $globalDisallowedProperties = null,
        ?array $allowedProperties = null,
        ?array $disallowedProperties = null,
        ?array $expectedDataProperties = null,
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
            \array_keys(\json_decode((string)$logEntries[0]['subject_data'], true))
        );
    }
}
