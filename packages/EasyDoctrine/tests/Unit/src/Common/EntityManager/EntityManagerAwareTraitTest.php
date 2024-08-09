<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\EntityManager;

use EonX\EasyDoctrine\Tests\Fixture\App\Processor\WithEntityManagerProcessor;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class EntityManagerAwareTraitTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $sut = self::getService(WithEntityManagerProcessor::class);

        self::assertSame(self::getEntityManager(), self::getPrivatePropertyValue($sut, 'entityManager'));
    }
}
