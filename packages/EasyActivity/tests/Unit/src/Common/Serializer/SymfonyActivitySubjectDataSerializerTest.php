<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\Common\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use EonX\EasyActivity\Bundle\Enum\ConfigServiceId;
use EonX\EasyActivity\Common\Entity\ActivitySubject;
use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Serializer\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Comment;
use EonX\EasyActivity\Tests\Fixture\App\ValueObject\AuthorExtra;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\NilUuid;

final class SymfonyActivitySubjectDataSerializerTest extends AbstractUnitTestCase
{
    /**
     * @see testSerializeSucceeds
     */
    public static function provideDataForSerializeSucceeds(): iterable
    {
        $entityId = (string)(new NilUuid());
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
            'subject' => new ActivitySubject($entityId, Author::class, [], [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"id":"00000000-0000-0000-0000-000000000000","name":"John Doe","position":1}',
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
            'subject' => new ActivitySubject($entityId, Author::class, $disallowedProperties, [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"name":"John Doe","position":1}',
        ];

        $disallowedProperties = [
            'id',
            'name',
            'position',
        ];
        yield 'Config with all properties are disallowed' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject($entityId, Author::class, $disallowedProperties, [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
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
            'subject' => new ActivitySubject($entityId, Author::class, [], [], [], []),
            'disallowedProperties' => $commonDisallowedProperties,
            'fullySerializableProperties' => [],
            'expectedResult' => '{"name":"John Doe","position":1}',
        ];

        $moment = new DateTimeImmutable();
        yield 'Config with nested object' => [
            'data' => [
                'author' => $author,
                'comments' => new ArrayCollection(),
                'content' => 'text',
                'createdAt' => $moment,
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject($entityId, Article::class, [], [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => \sprintf(
                '{"author":{"id":"00000000-0000-0000-0000-000000000000"},"comments":[],"content":"text",' .
                '"createdAt":"%s","id":"00000000-0000-0000-0000-000000000000"}',
                $moment->format(DateTimeInterface::ATOM)
            ),
        ];

        $allowedProperties = [
            'id',
            'name',
        ];
        yield 'Config with flat allowed_properties' => [
            'data' => [
                'id' => $entityId,
                'name' => $authorName,
                'position' => $authorPosition,
            ],
            'subject' => new ActivitySubject($entityId, Author::class, [], [], $allowedProperties, []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"id":"00000000-0000-0000-0000-000000000000","name":"John Doe"}',
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
                'createdAt' => new DateTimeImmutable(),
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject($entityId, Article::class, [], [], $allowedProperties, []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"author":{"id":"00000000-0000-0000-0000-000000000000","name":"John Doe"},' .
                '"content":"text"}',
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
                'createdAt' => new DateTimeImmutable(),
                'id' => $entityId,
            ],
            'subject' => new ActivitySubject(
                $entityId,
                Article::class,
                [],
                $nestedObjectAllowedProperties,
                $allowedProperties,
                []
            ),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"author":{"name":"John Doe"},"content":"text"}',
        ];

        $comment = (new Comment())
            ->setId((string)(new NilUuid()))
            ->setMessage('some-message');
        $article = new Article();
        $article->setId('00000000-0000-0000-0000-000000000001');
        $article->setAuthor($author);
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
            'subject' => new ActivitySubject($entityId, Article::class, [], [], $allowedProperties, []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => \sprintf(
                '{"comments":[{"article":{"author":{"id":"00000000-0000-0000-0000-000000000000","name":"John Doe"'
                . ',"position":1},"comments":["EonX\\\EasyActivity\\\Tests\\\Fixture' .
                '\\\App\\\Entity\\\Comment#00000000-0000-0000-0000'
                . '-000000000000 (circular reference)"],"createdAt":"%s","id":"00000000-0000-0000-0000-000000000001"},'
                . '"id":"00000000-0000-0000-0000-000000000000","message":"some-message"}]}',
                $expectedCreatedAt
            ),
        ];

        $authorExtra = (new AuthorExtra())
            ->setPhone('1234567890');

        yield 'Default config with nested object' => [
            'data' => [
                'name' => $authorName,
                'authorExtra' => $authorExtra,
            ],
            'subject' => new ActivitySubject($entityId, Author::class, [], [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"name":"John Doe","authorExtra":[]}',
        ];

        yield 'Config with global fullySerializableProperties' => [
            'data' => [
                'name' => $authorName,
                'authorExtra' => $authorExtra,
            ],
            'subject' => new ActivitySubject($entityId, Author::class, [], [], [], []),
            'disallowedProperties' => [],
            'fullySerializableProperties' => ['authorExtra'],
            'expectedResult' => '{"name":"John Doe","authorExtra":{"phone":"1234567890"}}',
        ];

        yield 'Config with subject fullySerializableProperties' => [
            'data' => [
                'name' => $authorName,
                'authorExtra' => $authorExtra,
            ],
            'subject' => new ActivitySubject($entityId, Author::class, [], [], [], ['authorExtra']),
            'disallowedProperties' => [],
            'fullySerializableProperties' => [],
            'expectedResult' => '{"name":"John Doe","authorExtra":{"phone":"1234567890"}}',
        ];
    }

    #[DataProvider('provideDataForSerializeSucceeds')]
    public function testSerializeSucceeds(
        array $data,
        ActivitySubjectInterface $subject,
        array $disallowedProperties,
        array $fullySerializableProperties,
        ?string $expectedResult,
    ): void {
        /** @var \EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandlerInterface $circularReferenceHandler */
        $circularReferenceHandler = self::getService(ConfigServiceId::CircularReferenceHandler->value);
        $serializer = new SymfonyActivitySubjectDataSerializer(
            self::getService(SerializerInterface::class),
            $circularReferenceHandler,
            $disallowedProperties,
            $fullySerializableProperties
        );

        $result = $serializer->serialize($data, $subject);

        self::assertEquals($expectedResult, $result);
    }
}
