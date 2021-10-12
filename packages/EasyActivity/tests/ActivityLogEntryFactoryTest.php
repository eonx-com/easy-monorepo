<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Carbon\Carbon;
use DateTime;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\Symfony\Normalizers\SymfonyNormalizer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\Stores\NullStore;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ActivityLogEntryFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testPropertyFilters
     */
    public function providerProperties(): iterable
    {
        yield 'only allowed properties' => [
            'globalDisallowProperties' => null,
            'entityAllowProperties' => ['title', 'content'],
            'entityDisallowProperties' => null,
            'expectedDataProperties' => ['title', 'content'],
        ];

        yield 'allowed and disallowed properties intersection' => [
            'globalDisallowProperties' => null,
            'entityAllowProperties' => ['title', 'content'],
            'entityDisallowProperties' => ['content'],
            'expectedDataProperties' => ['title'],
        ];

        yield 'only disallowed properties' => [
            'globalDisallowProperties' => null,
            'entityAllowProperties' => null,
            'entityDisallowProperties' => ['createdAt'],
            'expectedDataProperties' => ['title', 'author', 'content'],
        ];

        yield 'all properties are disallowed' => [
            'globalDisallowProperties' => null,
            'entityallowProperties' => null,
            'entityDisallowProperties' => ['title', 'createdAt', 'author', 'content'],
            'expectedDataProperties' => null,
        ];

        yield 'disallowed properties and defined on global and entity levels' => [
            'globalDisallowProperties' => ['createdAt'],
            'entityallowProperties' => null,
            'entityDisallowProperties' => ['title', 'author'],
            'expectedDataProperties' => ['content'],
        ];
    }

    public function testCreateReturnsNullWhenNoSubjectConfigured(): void
    {
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [],
            []
        );

        $result = $factory->create(ActivityLogEntry::ACTION_UPDATE, new Article(), ['key' => 'value']);

        self::assertNull($result);
    }

    public function testCreateSuccedes(): void
    {
        Carbon::setTestNow('2021-10-10 00:00:00');
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [Article::class => ['type' => 'article']],
            []
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
            ActivityLogEntry::ACTION_CREATE,
            new Article(),
            ['title' => 'New Title'],
            ['title' => 'Old Title']
        );

        self::assertNotNull($result);
        self::assertInstanceOf(ActivityLogEntry::class, $result);
        self::assertNull($result->getActorId());
        self::assertSame(
            '{"title":"New Title"}',
            $result->getData()
        );
        self::assertSame(
            '{"title":"Old Title"}',
            $result->getOldData()
        );
        self::assertSame(ActivityLogEntry::DEFAULT_ACTOR_TYPE, $result->getActorType());
        self::assertSame(ActivityLogEntry::ACTION_CREATE, $result->getAction());
        self::assertNull($result->getActorName());
        self::assertNull($result->getSubjectId());
        self::assertSame('article', $result->getSubjectType());
        self::assertEqualsCanonicalizing(Carbon::getTestNow(), $result->getCreatedAt());
        self::assertEqualsCanonicalizing(Carbon::getTestNow(), $result->getUpdatedAt());
    }

    public function testCreateSucceedsWithCollections(): void
    {
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [Article::class => ['type' => 'article']],
            []
        );
        $comment1 = (new Comment())
            ->setId(1)
            ->setMessage('Test 1');
        $comment2 = (new Comment())
            ->setId(2)
            ->setMessage('Test 2');
        $article = (new Article())
            ->setTitle('Related objects')
            ->addComment($comment1)
            ->addComment($comment2);

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        $result = $factory->create(
            ActivityLogEntry::ACTION_CREATE,
            new Article(),
            ['comments' => $article->getComments()]
        );

        self::assertNotNull($result);
        self::assertEquals(
            ['comments' => [['id' => 1], ['id' => 2]]],
            \json_decode((string)$result->getData(), true)
        );
    }

    public function testCreateSucceedsWithRelatedObjects(): void
    {
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [Article::class => ['type' => 'article']],
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
            new Article(),
            [
                'title' => $article->getTitle(),
                'author' => $article->getAuthor(),
            ]
        );

        self::assertNotNull($result);
        self::assertEquals(
            [
                'title' => 'Related objects',
                'author' => ['id' => 2],
            ],
            \json_decode((string)$result->getData(), true)
        );
    }

    public function testCreateThrowsErrorWhenChangeSetHasNotSerializableValue(): void
    {
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [Article::class => ['type' => 'article']],
            []
        );

        $this->safeCall(function () use ($factory) {
            $factory->create(ActivityLogEntry::ACTION_UPDATE, new Article(), ['value' => \curl_init()]);
        });

        $this->assertThrownException(NotNormalizableValueException::class, 0);
    }

    /**
     * @param string[]|null $globalDisallowedProperties
     * @param string[]|null $allowedProperties
     * @param string[]|null $disallowedProperties
     * @param string[]|null $expectedDataProperties
     *
     * @dataProvider providerProperties
     */
    public function testPropertyFilters(
        ?array $globalDisallowedProperties = null,
        ?array $allowedProperties = null,
        ?array $disallowedProperties = null,
        ?array $expectedDataProperties = null
    ): void {
        $factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new NullStore(),
            new SymfonyNormalizer(new Serializer([new ObjectNormalizer()])),
            [
                Article::class => [
                    'type' => 'article',
                    'allowed_properties' => $allowedProperties,
                    'disallowed_properties' => $disallowedProperties,
                ],
            ],
            $globalDisallowedProperties
        );
        $author = new Author();
        $author->setName('John');
        $author->setPosition(1);
        $author->setId(1);

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            new Article(),
            [
                'title' => 'Title',
                'content' => 'Content',
                'author' => $author,
                'createdAt' => new DateTime('2021-10-10 00:00:00'),
            ],
            [
                'title' => 'Title 2',
                'content' => 'Content 2',
                'author' => $author,
                'createdAt' => new DateTime('2021-10-10 00:00:00'),
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
            \array_keys(\json_decode($result->getData() ?? '', true))
        );
    }
}
