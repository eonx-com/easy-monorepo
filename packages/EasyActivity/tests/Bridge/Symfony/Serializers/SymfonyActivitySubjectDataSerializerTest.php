<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Serializers;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use EonX\EasyActivity\ActivitySubject;
use EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandler;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonyActivitySubjectDataSerializerTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testSerializeSucceeds
     */
    public function provideDataForSerializeSucceeds(): iterable
    {
        $entityId = 1;
        $authorName = 'John Doe';
        $authorPosition = 1;
        $author = new Author();
        $author->setId($entityId);
        $author->setName($authorName);
        $author->setPosition($authorPosition);

        yield 'Default config' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, [], [], []),
            'disallowedProperties' => null,
            'expectedResult' => '{"id":1,"name":"John Doe","position":1}',
        ];

        $disallowedProperties = [
            'id',
        ];
        yield 'Config with disallowed_properties' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, $disallowedProperties, [], []),
            'disallowedProperties' => null,
            'expectedResult' => '{"name":"John Doe","position":1}',
        ];

        $disallowedProperties = [
            'id', 'name', 'position',
        ];
        yield 'Config with all properties are disallowed' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, $disallowedProperties, [], []),
            'disallowedProperties' => null,
            'expectedResult' => null,
        ];

        $commonDisallowedProperties = [
            'id',
        ];
        yield 'Config with common disallowed_properties' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, [], [], []),
            'disallowedProperties' => $commonDisallowedProperties,
            'expectedResult' => '{"name":"John Doe","position":1}',
        ];

        $moment = new \DateTimeImmutable();
        yield 'Config with nested object' => [
            'data' => [
                'author' => $author,
                'comments' => new ArrayCollection(),
                'content' => 'text',
                'createdAt' => $moment,
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject((string)$entityId, Article::class, [], [], []),
            'disallowedProperties' => null,
            'expectedResult' => \sprintf(
                '{"author":{"id":1},"comments":[],"content":"text","createdAt":"%s","id":1}',
                $moment->format(DateTimeInterface::ATOM)
            ),
        ];

        yield 'Config with null allowed_properties' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, [], [], null),
            'disallowedProperties' => null,
            'expectedResult' => null,
        ];

        $allowedProperties = [
            'id', 'name',
        ];
        yield 'Config with flat allowed_properties' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject((string)$entityId, Author::class, [], [], $allowedProperties),
            'disallowedProperties' => null,
            'expectedResult' => '{"id":1,"name":"John Doe"}',
        ];

        $allowedProperties = [
            'author' => ['id', 'name'],
            'content',
        ];
        yield 'Config with nested allowed_properties' => [
            'data' => [
                'author' => $author,
                'comment' => new ArrayCollection(),
                'content' => 'text',
                'createdAt' => new \DateTimeImmutable(),
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject((string)$entityId, Article::class, [], [], $allowedProperties),
            'disallowedProperties' => null,
            'expectedResult' => '{"author":{"id":1,"name":"John Doe"},"content":"text"}',
        ];

        $allowedProperties = [
            'author' => ['id', 'name'],
            'content',
        ];
        $nestedObjectAllowedProperties = [
            Author::class => ['name'],
        ];
        yield 'Config with nested allowed_properties and nested_object_allowed_properties' => [
            'data' => [
                'author' => $author,
                'comment' => new ArrayCollection(),
                'content' => 'text',
                'createdAt' => new \DateTimeImmutable(),
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject(
                (string)$entityId,
                Article::class,
                [],
                $nestedObjectAllowedProperties,
                $allowedProperties
            ),
            'disallowedProperties' => null,
            'expectedResult' => '{"author":{"name":"John Doe"},"content":"text"}',
        ];

        $comment = (new Comment())
            ->setId(1)
            ->setMessage('some-message');
        $article = new Article();
        $article->addComment($comment);
        $allowedProperties = [
            'comments' => ['article'],
        ];
        $expectedCreatedAt = $article->getCreatedAt()
            ->format(DateTimeInterface::ATOM);
        yield 'With circular reference' => [
            'data' => [
                'comments' => [$comment],
            ],
            'subject' => new ActivitySubject((string)$entityId, Article::class, [], [], $allowedProperties),
            'disallowedProperties' => null,
            'expectedResult' => \sprintf(
                '{"comments":[{"article":{"author":null,"comments":' .
                '["EonX\\\EasyActivity\\\Tests\\\Fixtures\\\Comment#1 (circular reference)"],' .
                '"createdAt":"%s","id":null},"id":1,"message":"some-message"}]}',
                $expectedCreatedAt
            ),
        ];
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed>|null $disallowedProperties
     *
     * @dataProvider provideDataForSerializeSucceeds
     */
    public function testSerializeSucceeds(
        array $data,
        ActivitySubjectInterface $subject,
        ?array $disallowedProperties,
        ?string $expectedResult
    ): void {
        $symfonySerializer = $this->arrangeSymfonySerializer();
        $serializer = new SymfonyActivitySubjectDataSerializer(
            $symfonySerializer,
            new CircularReferenceHandler(EntityManagerStub::createFromEventManager()),
            $disallowedProperties ?? []
        );

        $result = $serializer->serialize($data, $subject);

        self::assertEquals($expectedResult, $result);
    }

    private function arrangeSymfonySerializer(): SerializerInterface
    {
        $container = $this->getKernel()
            ->getContainer();
        /** @var SerializerInterface $symfonySerializer */
        $symfonySerializer = $container->get(SerializerInterface::class);

        return $symfonySerializer;
    }
}
