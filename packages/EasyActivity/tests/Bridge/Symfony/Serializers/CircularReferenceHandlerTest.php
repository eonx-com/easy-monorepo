<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Serializers;

use EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandler;
use EonX\EasyActivity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;
use stdClass;

/**
 * @covers \EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandler
 */
final class CircularReferenceHandlerTest extends AbstractSymfonyTestCase
{
    public function testInvokeSucceedsWithId(): void
    {
        $entityManager = EntityManagerStub::createFromEventManager();
        $handler = new CircularReferenceHandler($entityManager);
        $article = (new Article())->setId(1);

        $result = $handler($article, 'json', []);

        self::assertSame('EonX\EasyActivity\Tests\Fixtures\Article#1 (circular reference)', $result);
    }

    public function testInvokeSucceedsWithoutId(): void
    {
        $entityManager = EntityManagerStub::createFromEventManager();
        $handler = new CircularReferenceHandler($entityManager);
        $object = new stdClass();

        $result = $handler($object, 'json', []);

        self::assertSame('stdClass (circular reference)', $result);
    }
}
