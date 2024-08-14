<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Listener;

use Carbon\CarbonImmutable;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class TimestampableListenerTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $now = CarbonImmutable::parse('2021-11-24 12:23:34');
        CarbonImmutable::setTestNow($now);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $author = (new Category())
            ->setName('Some Name');
        $entityManager->persist($author);

        $entityManager->flush();

        self::assertEntityExists(Category::class, [
            'name' => 'Some Name',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
    }
}
