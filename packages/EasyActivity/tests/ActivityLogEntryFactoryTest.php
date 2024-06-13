<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Carbon\Carbon;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Article;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Author;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Comment;
use Symfony\Component\Uid\NilUuid;

final class ActivityLogEntryFactoryTest extends AbstractTestCase
{
    public function testCreateReturnsNullWhenNoSubjectConfigured(): void
    {
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);

        $result = $sut->create(ActivityLogEntry::ACTION_UPDATE, new Article(), ['key' => 'value']);

        self::assertNull($result);
    }

    /**
     * @see packages/EasyActivity/tests/Bridge/Symfony/Fixtures/app/config/packages/default_subject_config
     */
    public function testCreateSucceeds(): void
    {
        self::bootKernel(['environment' => 'default_subject_config']);
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);

        $result = $sut->create(
            ActivityLogEntry::ACTION_CREATE,
            (new Article())->setId((string)(new NilUuid())),
            ['title' => [null, 'New Title']]
        );

        self::assertNotNull($result);
        self::assertInstanceOf(ActivityLogEntry::class, $result);
        self::assertNull($result->getActorId());
        self::assertSame(
            '{"title":"New Title"}',
            $result->getSubjectData()
        );
        self::assertNull($result->getSubjectOldData());
        self::assertSame(ActivityLogEntry::DEFAULT_ACTOR_TYPE, $result->getActorType());
        self::assertSame(ActivityLogEntry::ACTION_CREATE, $result->getAction());
        self::assertNull($result->getActorName());
        self::assertSame((string)(new NilUuid()), $result->getSubjectId());
        self::assertSame(Article::class, $result->getSubjectType());
        self::assertEquals($now, $result->getCreatedAt());
        self::assertEquals($now, $result->getUpdatedAt());
    }

    /**
     * @see packages/EasyActivity/tests/Bridge/Symfony/Fixtures/app/config/packages/default_subject_config
     */
    public function testCreateSucceedsWithCollections(): void
    {
        self::bootKernel(['environment' => 'default_subject_config']);
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);
        $comment1 = (new Comment())
            ->setId((string)(new NilUuid()))
            ->setMessage('Test 1');
        $comment2 = (new Comment())
            ->setId('00000000-0000-0000-0000-000000000001')
            ->setMessage('Test 2');
        $article = (new Article())
            ->setId('00000000-0000-0000-0000-000000000002')
            ->setTitle('Related objects')
            ->setContent('Content')
            ->addComment($comment1)
            ->addComment($comment2);

        $result = $sut->create(
            ActivityLogEntry::ACTION_CREATE,
            $article,
            [
                'content' => [null, $article->getContent()],
                'comments' => [null, $article->getComments()],
            ]
        );

        self::assertNotNull($result);
        self::assertEquals(
            [
                'content' => 'Content',
                'comments' => [
                    ['id' => (string)(new NilUuid())],
                    ['id' => '00000000-0000-0000-0000-000000000001'],
                ],
            ],
            \json_decode((string)$result->getSubjectData(), true)
        );
    }

    /**
     * @see packages/EasyActivity/tests/Bridge/Symfony/Fixtures/app/config/packages/default_subject_config
     */
    public function testCreateSucceedsWithRelatedObjects(): void
    {
        self::bootKernel(['environment' => 'default_subject_config']);
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);
        $author = new Author();
        $author->setId((string)(new NilUuid()));
        $author->setName('John');
        $author->setPosition(1);
        $article = new Article();
        $article->setId('00000000-0000-0000-0000-000000000001');
        $article->setTitle('Related objects');
        $article->setAuthor($author);

        $result = $sut->create(
            ActivityLogEntry::ACTION_CREATE,
            $article,
            [
                'title' => [null, $article->getTitle()],
                'author' => [null, $article->getAuthor()],
            ]
        );

        self::assertNotNull($result);
        self::assertEquals(
            [
                'title' => 'Related objects',
                'author' => ['id' => (string)(new NilUuid())],
            ],
            \json_decode((string)$result->getSubjectData(), true)
        );
    }

    /**
     * @see packages/EasyActivity/tests/Bridge/Symfony/Fixtures/app/config/packages/nested_object_allowed_properties
     */
    public function testCreateSucceedsWithRelatedObjectsWhenConfiguredNestedObjectAllowedProperties(): void
    {
        self::bootKernel(['environment' => 'nested_object_allowed_properties']);
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);
        $author = new Author();
        $author->setId((string)(new NilUuid()));
        $author->setName('John');
        $author->setPosition(1);
        $article = new Article();
        $article->setId('00000000-0000-0000-0000-000000000001');
        $article->setTitle('Related objects');
        $article->setAuthor($author);

        $result = $sut->create(
            ActivityLogEntry::ACTION_CREATE,
            $article,
            [
                'title' => [null, 'Related objects'],
                'author' => [null, $article->getAuthor()],
            ]
        );

        self::assertNotNull($result);
        self::assertEquals(
            [
                'title' => 'Related objects',
                'author' => [
                    'name' => 'John',
                    'position' => 1,
                ],
            ],
            \json_decode((string)$result->getSubjectData(), true)
        );
    }
}
