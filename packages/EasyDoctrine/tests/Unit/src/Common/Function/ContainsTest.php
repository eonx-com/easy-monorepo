<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Function;

use EonX\EasyDoctrine\Common\Function\Contains;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Contains::class)]
final class ContainsTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $entityManager = self::getEntityManager();

        $sql = $entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('CONTAINS(c.name, :someParameter)')
            ->getQuery()
            ->getSQL();

        self::assertSame('SELECT (c0_.name @> ?) AS sclr_0 FROM category c0_', $sql);
    }
}
