<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Serializers;

use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use EonX\EasyActivity\Tests\AbstractTestCase;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Article;
use stdClass;
use Symfony\Component\Uid\NilUuid;

final class CircularReferenceHandlerTest extends AbstractTestCase
{
    public function testInvokeSucceedsWithId(): void
    {
        $article = (new Article())->setId((string)(new NilUuid()));
        /** @var \EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandlerInterface $sut */
        $sut = self::getService(BridgeConstantsInterface::SERVICE_CIRCULAR_REFERENCE_HANDLER);

        $result = $sut($article, 'json', []);

        self::assertSame(
            Article::class . '#00000000-0000-0000-0000-000000000000 (circular reference)',
            $result
        );
    }

    public function testInvokeSucceedsWithoutId(): void
    {
        $object = new stdClass();
        /** @var \EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandlerInterface $sut */
        $sut = self::getService(BridgeConstantsInterface::SERVICE_CIRCULAR_REFERENCE_HANDLER);

        $result = $sut($object, 'json', []);

        self::assertSame('stdClass (circular reference)', $result);
    }
}
