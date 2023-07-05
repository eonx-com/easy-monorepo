<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Carbon\Carbon;
use DateTime;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyActivity\Tests\Stubs\ActivityLogFactoryStub;

final class ActivityLogEntryFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testPropertyFilters
     */
    public static function providerProperties(): iterable
    {
        yield 'only allowed properties' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => ['title', 'content'],
            'entityDisallowProperties' => [],
            'expectedDataProperties' => ['title', 'content'],
        ];

        yield 'allowed and disallowed properties intersection' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => ['title', 'content'],
            'entityDisallowProperties' => ['content'],
            'expectedDataProperties' => ['title'],
        ];

        yield 'only disallowed properties' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => [],
            'entityDisallowProperties' => ['createdAt'],
            'expectedDataProperties' => ['title', 'author', 'content'],
        ];

        yield 'all properties are disallowed' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => [],
            'entityDisallowProperties' => ['title', 'createdAt', 'author', 'content'],
            'expectedDataProperties' => null,
        ];

        yield 'disallowed properties and defined on global and entity levels' => [
            'globalDisallowProperties' => ['createdAt'],
            'entityAllowProperties' => [],
            'entityDisallowProperties' => ['title', 'author'],
            'expectedDataProperties' => ['content'],
        ];

        yield 'empty allowed properties' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => [],
            'entityDisallowProperties' => [],
            'expectedDataProperties' => ['title', 'createdAt', 'author', 'content'],
        ];

        yield 'allowed properties are null' => [
            'globalDisallowProperties' => [],
            'entityAllowProperties' => null,
            'entityDisallowProperties' => [],
            'expectedDataProperties' => null,
        ];
    }

    public function testCreateReturnsNullWhenNoSubjectConfigured(): void
    {
        $factory = new ActivityLogFactoryStub([], []);

        $result = $factory->create(ActivityLogEntry::ACTION_UPDATE, new Article(), ['key' => 'value']);

        self::assertNull($result);
    }

    public function testCreateSucceeds(): void
    {
        Carbon::setTestNow('2021-10-10 00:00:00');
        $factory = new ActivityLogFactoryStub([Article::class => []], []);

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
            ActivityLogEntry::ACTION_CREATE,
            (new Article())->setId(2),
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
        self::assertSame('2', $result->getSubjectId());
        self::assertSame(Article::class, $result->getSubjectType());
        self::assertEqualsCanonicalizing(Carbon::getTestNow(), $result->getCreatedAt());
        self::assertEqualsCanonicalizing(Carbon::getTestNow(), $result->getUpdatedAt());
    }

    public function testCreateSucceedsWithCollections(): void
    {
        $factory = new ActivityLogFactoryStub([Article::class => []], []);
        $comment1 = (new Comment())
            ->setId(1)
            ->setMessage('Test 1');
        $comment2 = (new Comment())
            ->setId(2)
            ->setMessage('Test 2');
        $article = (new Article())
            ->setTitle('Related objects')
            ->setContent('Content')
            ->addComment($comment1)
            ->addComment($comment2);

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
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
                'comments' => [['id' => 1], ['id' => 2]],
            ],
            \json_decode((string)$result->getSubjectData(), true)
        );
    }

    public function testCreateSucceedsWithRelatedObjects(): void
    {
        $factory = new ActivityLogFactoryStub([Article::class => []], []);
        $author = new Author();
        $author->setId(2);
        $author->setName('John');
        $author->setPosition(1);
        $article = new Article();
        $article->setTitle('Related objects');
        $article->setAuthor($author);

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
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
                'author' => ['id' => 2],
            ],
            \json_decode((string)$result->getSubjectData(), true)
        );
    }

    public function testCreateSucceedsWithRelatedObjects2(): void
    {
        $factory = new ActivityLogFactoryStub(
            [
                Article::class => [
                    'allowed_properties' => [
                        'title',
                        'author' => ['name', 'position'],
                    ],
                ],
            ],
            []
        );
        $author = new Author();
        $author->setId(2);
        $author->setName('John');
        $author->setPosition(1);
        $article = new Article();
        $article->setTitle('Related objects');
        $article->setAuthor($author);

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
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

    /**
     * @param string[] $globalDisallowedProperties
     * @param string[]|null $allowedProperties
     * @param string[] $disallowedProperties
     * @param string[]|null $expectedDataProperties
     *
     * @dataProvider providerProperties
     */
    public function testPropertyFilters(
        array $globalDisallowedProperties,
        ?array $allowedProperties,
        array $disallowedProperties,
        ?array $expectedDataProperties = null,
    ): void {
        $factory = new ActivityLogFactoryStub([
            Article::class => [
                'allowed_properties' => $allowedProperties,
                'disallowed_properties' => $disallowedProperties,
            ],
        ], $globalDisallowedProperties);
        $author = new Author();
        $author->setName('John');
        $author->setPosition(1);
        $author->setId(1);

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            new Article(),
            [
                'title' => ['Title 2', 'Title'],
                'content' => ['Content 2', 'Content'],
                'author' => [$author, $author],
                'createdAt' => [
                    new DateTime('2021-10-10 00:00:00'),
                    new DateTime('2021-10-10 00:00:00'),
                ],
            ]
        );

        if ($expectedDataProperties === null) {
            self::assertNull($result);

            return;
        }
        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertEqualsCanonicalizing(
            $expectedDataProperties,
            \array_keys(\json_decode($result->getSubjectData() ?? '', true))
        );
    }
}
