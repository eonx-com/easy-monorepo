<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\Common\CircularReferenceHandler;

use EonX\EasyActivity\Bundle\Enum\ConfigServiceId;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use stdClass;
use Symfony\Component\Uid\NilUuid;

final class CircularReferenceHandlerTest extends AbstractUnitTestCase
{
    public function testInvokeSucceedsWithId(): void
    {
        $article = (new Article())->setId((string)(new NilUuid()));
        /** @var \EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandlerInterface $sut */
        $sut = self::getService(ConfigServiceId::CircularReferenceHandler->value);

        $result = $sut($article, 'json', []);

        self::assertSame(
            Article::class . '#00000000-0000-0000-0000-000000000000 (circular reference)',
            $result
        );
    }

    public function testInvokeSucceedsWithoutId(): void
    {
        $object = new stdClass();
        /** @var \EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandlerInterface $sut */
        $sut = self::getService(ConfigServiceId::CircularReferenceHandler->value);

        $result = $sut($object, 'json', []);

        self::assertSame('stdClass (circular reference)', $result);
    }
}
