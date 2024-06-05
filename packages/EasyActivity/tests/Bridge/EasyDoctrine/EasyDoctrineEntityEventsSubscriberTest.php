<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\EasyDoctrine;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Tests\AbstractTestCase;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\ActivityLog;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Article;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Author;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Comment;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyDoctrineEntityEventsSubscriberTest extends AbstractTestCase
{
    /**
     * @see testPropertyFilters
     */
    public static function provideProperties(): iterable
    {
        yield 'only allowed properties' => [
            'environment' => 'case6_1',
            'expectedProperties' => ['title', 'content'],
        ];

        yield 'allowed and disallowed properties intersection' => [
            'environment' => 'case6_2',
            'expectedProperties' => ['title'],
        ];

        yield 'only disallowed properties' => [
            'environment' => 'case6_3',
            'expectedProperties' => ['title', 'author', 'id', 'content'],
        ];

        yield 'all properties are disallowed' => [
            'environment' => 'case6_4',
            'expectedProperties' => null,
        ];

        yield 'disallowed properties and defined on global and entity levels' => [
            'environment' => 'case6_5',
            'expectedProperties' => ['content', 'id'],
        ];
    }

    public function testLoggerDoesNothingWhenSubjectIsNotDefinedInConfig(): void
    {
        self::bootKernel();
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $article = (new Article())
            ->setTitle('Resolver')
            ->setContent('Test actor resolver');
        $entityManager->persist($article);
        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 0);
    }

    public function testLoggerSucceedsForDeletedSubjects(): void
    {
        self::bootKernel(['environment' => 'case1']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $now = CarbonImmutable::now();
        Carbon::setTestNow($now);
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

        self::assertEntityCount(ActivityLog::class, 2);
        self::assertEntityExists(
            ActivityLog::class,
            [
                'action' => ActivityLogEntry::ACTION_DELETE,
                'actorId' => null,
                'actorName' => null,
                'actorType' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
                'subjectId' => $articleId,
                'subjectType' => Article::class,
                'createdAt' => $now,
                'updatedAt' => $now,
                'subjectData' => null,
                'subjectOldData' => \json_encode([
                    'content' => 'Content',
                    'createdAt' => $now->toAtomString(),
                    'id' => $articleId,
                    'title' => 'Title 1',
                    'author' => [
                        'id' => $authorId,
                    ],
                    'comments' => [],
                ]),
            ],
        );
    }

    public function testLoggerSucceedsForSubjectsCreatedInTransaction(): void
    {
        self::bootKernel(['environment' => 'case2']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $now = CarbonImmutable::now();
        Carbon::setTestNow($now);

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

        self::assertEntityCount(ActivityLog::class, 1);
        self::assertEntityExists(
            ActivityLog::class,
            [
                'actorType' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
                'actorId' => null,
                'actorName' => null,
                'action' => ActivityLogEntry::ACTION_CREATE,
                'subjectType' => 'article',
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'content' => 'Content 2',
                    'title' => 'Title 3',
                ]),
                'subjectOldData' => null,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]
        );
    }

    public function testLoggerSucceedsForUpdatedSubjects(): void
    {
        self::bootKernel(['environment' => 'case2']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $now = CarbonImmutable::now();
        Carbon::setTestNow($now);

        $article = new Article();
        $article->setTitle('Title 1');
        $article->setContent('Content');
        $entityManager->persist($article);
        $entityManager->flush();
        $article->setTitle('Title 2');
        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 2);
        self::assertEntityExists(
            ActivityLog::class,
            [
                'actorType' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
                'actorId' => null,
                'actorName' => null,
                'action' => ActivityLogEntry::ACTION_CREATE,
                'subjectType' => 'article',
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'content' => 'Content',
                    'title' => 'Title 1',
                ]),
                'subjectOldData' => null,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]
        );
        self::assertEntityExists(
            ActivityLog::class,
            [
                'actorType' => ActivityLogEntry::DEFAULT_ACTOR_TYPE,
                'actorId' => null,
                'actorName' => null,
                'action' => ActivityLogEntry::ACTION_UPDATE,
                'subjectType' => 'article',
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'title' => 'Title 2',
                ]),
                'subjectOldData' => \json_encode([
                    'title' => 'Title 1',
                ]),
                'createdAt' => $now,
                'updatedAt' => $now,
            ]
        );
    }

    public function testLoggerSucceedsWithCollections(): void
    {
        self::bootKernel(['environment' => 'case3']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
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

        self::assertEntityCount(ActivityLog::class, 4);
        // Create an article
        self::assertEntityExists(
            ActivityLog::class,
            [
                'action' => ActivityLogEntry::ACTION_CREATE,
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'title' => 'Test collections',
                    'comments' => [$commentA->getId(), $commentB->getId(), $commentC->getId(), $commentDId],
                ]),
            ]
        );
        // Remove the comment C from collection
        self::assertEntityExists(
            ActivityLog::class,
            [
                'action' => ActivityLogEntry::ACTION_UPDATE,
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId(), $commentDId],
                ]),
                'subjectOldData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId(), $commentC->getId(), $commentDId],
                ]),
            ]
        );
        // Remove the comment D entity
        self::assertEntityExists(
            ActivityLog::class,
            [
                'action' => ActivityLogEntry::ACTION_UPDATE,
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId()],
                ]),
                'subjectOldData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId(), $commentDId],
                ]),
            ]
        );
        // Add a new comment E to the collection
        self::assertEntityExists(
            ActivityLog::class,
            [
                'action' => ActivityLogEntry::ACTION_UPDATE,
                'subjectId' => $article->getId(),
                'subjectData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId(), $commentE->getId()],
                ]),
                'subjectOldData' => \json_encode([
                    'comments' => [$commentA->getId(), $commentB->getId()],
                ]),
            ]
        );
    }

    public function testLoggerSucceedsWithCustomActorResolver(): void
    {
        self::bootKernel(['environment' => 'case4']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $article = new Article();
        $article->setTitle('Resolver');
        $article->setContent('Test actor resolver');

        $entityManager->persist($article);
        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 1);
        self::assertEntityExists(
            ActivityLog::class,
            [
                'actorType' => 'actor-type',
                'actorId' => 'actor-id',
                'actorName' => 'actor-name',
                'subjectId' => $article->getId(),
            ]
        );
    }

    public function testLoggerSucceedsWithRelatedObjects(): void
    {
        self::bootKernel(['environment' => 'case5']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
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

        self::assertEntityCount(ActivityLog::class, 2);
        self::assertEntityExists(
            ActivityLog::class,
            [
                'subjectData' => \json_encode([
                    'id' => $author->getId(),
                    'name' => 'John',
                    'position' => 1,
                ]),
            ]
        );
        self::assertEntityExists(
            ActivityLog::class,
            [
                'subjectData' => \json_encode([
                    'title' => 'Resolver',
                    'author' => ['id' => $author->getId()],
                ]),
            ]
        );
    }

    /**
     * @param string[] $globalDisallowedProperties
     * @param string[] $allowedProperties
     * @param string[] $disallowedProperties
     * @param string[] $expectedProperties
     */
    #[DataProvider('provideProperties')]
    public function testPropertyFilters(
        string $environment,
        ?array $expectedProperties = null,
    ): void {
        self::bootKernel(['environment' => $environment]);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $article = new Article();
        $article->setTitle('Title');
        $article->setContent('Content');

        $entityManager->persist($article);
        $entityManager->flush();

        if ($expectedProperties === null) {
            self::assertEntityCount(ActivityLog::class, 0);

            return;
        }
        self::assertEntityCount(ActivityLog::class, 1);
        $activityLog = self::findOneEntity(ActivityLog::class, []);
        self::assertEqualsCanonicalizing(
            $expectedProperties,
            \array_keys(\json_decode($activityLog->getSubjectData(), true))
        );
    }
}
