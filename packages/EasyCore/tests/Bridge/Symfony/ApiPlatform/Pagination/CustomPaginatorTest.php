<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginator;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;

final class CustomPaginatorTest extends AbstractSymfonyTestCase
{
    public function testCustomPaginator(): void
    {
        $paginator = new CustomPaginator($this->getApiPlatformPaginator());

        $expectedPagination = [
            'currentPage' => 1,
            'hasNextPage' => false,
            'hasPreviousPage' => false,
            'itemsPerPage' => 15,
            'totalItems' => 0,
            'totalPages' => 1,
        ];

        self::assertEmpty($paginator->getItems());
        self::assertEquals($expectedPagination, $paginator->getPagination());
    }

    /**
     * @return \ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator<mixed>
     */
    private function getApiPlatformPaginator(): Paginator
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $manager */
        $manager = $this->mock(EntityManagerInterface::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getConfiguration')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn(new Configuration());
        });

        $query = new Query($manager);
        $query->setFirstResult(1)
            ->setMaxResults(15);

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $doctrinePaginator */
        $doctrinePaginator = $this->mock(
            DoctrinePaginator::class,
            static function (MockInterface $mock) use ($query): void {
                $mock->shouldReceive('getQuery')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($query);
                $mock->shouldReceive('getIterator')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(new \ArrayIterator());
                $mock->shouldReceive('count')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(0);
            }
        );

        return new Paginator($doctrinePaginator);
    }
}
