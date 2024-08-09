<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Function;

use EonX\EasyDoctrine\Common\Function\StringAgg;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StringAgg::class)]
final class StringAggTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $entityManager = self::getEntityManager();

        $sql = $entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select("STRING_AGG(c.id, ',')")
            ->getQuery()
            ->getSQL();

        self::assertSame("SELECT STRING_AGG(c0_.id::CHARACTER VARYING, ',') AS sclr_0 FROM category c0_", $sql);
    }

    public function testItSucceedsWithDistinct(): void
    {
        $entityManager = self::getEntityManager();

        $sql = $entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select("STRING_AGG(DISTINCT c.id, ',')")
            ->getQuery()
            ->getSQL();

        self::assertSame(
            "SELECT STRING_AGG(DISTINCT c0_.id::CHARACTER VARYING, ',') AS sclr_0 FROM category c0_",
            $sql
        );
    }

    public function testItSucceedsWithOrderBy(): void
    {
        $entityManager = self::getEntityManager();

        $sql = $entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select("STRING_AGG(c.id, ',' ORDER BY c.name)")
            ->getQuery()
            ->getSQL();

        self::assertSame(
            "SELECT STRING_AGG(c0_.id::CHARACTER VARYING, ',' ORDER BY c0_.name ASC) AS sclr_0 FROM category c0_",
            $sql
        );
    }
}
