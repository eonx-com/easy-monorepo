<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\Bundle;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Comment;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;
use stdClass;

final class EasyActivityBundleTest extends AbstractUnitTestCase
{
    public function testItSucceedsPrependConfig(): void
    {
        self::bootKernel(['environment' => 'config_prepend']);
        $sut = self::getService(EntityEventListener::class);

        $trackableEntities = self::getPrivatePropertyValue($sut, 'trackableEntities');

        self::assertSame(
            [
                stdClass::class,
                Article::class,
                Comment::class,
            ],
            $trackableEntities
        );
    }
}
