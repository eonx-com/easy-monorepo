<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Function;

use EonX\EasyDoctrine\Common\Function\Cast;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cast::class)]
final class CastTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $entityManager = self::getEntityManager();

        $sql = $entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select("CAST(c.id, 'text')")
            ->getQuery()
            ->getSQL();

        self::assertSame('SELECT CAST(c0_.id AS text) AS sclr_0 FROM category c0_', $sql);
    }
}
