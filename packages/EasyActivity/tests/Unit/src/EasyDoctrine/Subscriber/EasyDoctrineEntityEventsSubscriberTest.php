<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyActivity\Tests\Fixture\App\Entity\ActivityLog;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Comment;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyDoctrineEntityEventsSubscriberTest extends AbstractUnitTestCase
{
    /**
     * @see testPropertyFilters
     */
    public static function provideProperties(): iterable
    {
        /**
         * @see packages/EasyActivity/tests/Fixture/app/config/packages/only_allowed_properties
         */
        yield 'only allowed properties' => [
            'environment' => 'only_allowed_properties',
            'expectedProperties' => ['title', 'content'],
        ];

        /**
         * @see packages/EasyActivity/tests/Fixture/app/config/packages/allowed_and_disallowed_properties_intersection
         */
        yield 'allowed and disallowed properties intersection' => [
            'environment' => 'allowed_and_disallowed_properties_intersection',
            'expectedProperties' => ['title'],
        ];

        /**
         * @see packages/EasyActivity/tests/Fixture/app/config/packages/only_disallowed_properties
         */
        yield 'only disallowed properties' => [
            'environment' => 'only_disallowed_properties',
            'expectedProperties' => ['title', 'author', 'id', 'content'],
        ];

        /**
         * @see packages/EasyActivity/tests/Fixture/app/config/packages/all_properties_are_disallowed
         */
        yield 'all properties are disallowed' => [
            'environment' => 'all_properties_are_disallowed',
            'expectedProperties' => null,
        ];

        /**
         * @see packages/EasyActivity/tests/Fixture/app/config/packages/global_and_subject_disallowed_properties
         */
        yield 'disallowed properties and defined on global and entity levels' => [
            'environment' => 'global_and_subject_disallowed_properties',
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

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/default_subject_config
     */
    public function testLoggerSucceedsForDeletedSubjects(): void
    {
        self::bootKernel(['environment' => 'default_subject_config']);
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

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/custom_subject_type
     */
    public function testLoggerSucceedsForSubjectsCreatedInTransaction(): void
    {
        self::bootKernel(['environment' => 'custom_subject_type']);
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
                    'createdAt' => $now->toAtomString(),
                    'id' => $article->getId(),
                    'title' => 'Title 3',
                    'author' => null,
                ]),
                'subjectOldData' => null,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]
        );
    }

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/custom_subject_type
     */
    public function testLoggerSucceedsForUpdatedSubjects(): void
    {
        self::bootKernel(['environment' => 'custom_subject_type']);
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
                    'createdAt' => $now->toAtomString(),
                    'id' => $article->getId(),
                    'title' => 'Title 1',
                    'author' => null,
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

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/custom_subject_type
     */
    public function testLoggerSucceedsWithCollections(): void
    {
        self::bootKernel(['environment' => 'custom_subject_type']);
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
                    'content' => 'Content',
                    'createdAt' => $article->getCreatedAt()
                        ->format('c'),
                    'id' => $article->getId(),
                    'title' => 'Test collections',
                    'author' => null,
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

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/custom_actor_resolver
     */
    public function testLoggerSucceedsWithCustomActorResolver(): void
    {
        self::bootKernel(['environment' => 'custom_actor_resolver']);
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

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/related_object_in_subjects
     */
    public function testLoggerSucceedsWithRelatedObjects(): void
    {
        self::bootKernel(['environment' => 'related_object_in_subjects']);
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
                    'content' => 'Test actor resolver',
                    'createdAt' => $article->getCreatedAt()
                        ->format('c'),
                    'id' => $article->getId(),
                    'title' => 'Resolver',
                    'author' => ['id' => $author->getId()],
                ]),
            ]
        );
    }

    /**
     * @param string[]|null $expectedProperties
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
        self::assertNotNull($activityLog);
        self::assertEqualsCanonicalizing(
            $expectedProperties,
            \array_keys(\json_decode((string)$activityLog->getSubjectData(), true))
        );
    }
}
