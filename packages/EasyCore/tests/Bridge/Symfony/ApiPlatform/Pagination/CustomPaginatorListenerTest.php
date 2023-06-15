<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Doctrine\Orm\Paginator;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginationListener;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginatorInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CustomPaginatorListenerTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testListener
     */
    public function providerTestListener(): iterable
    {
        yield 'Not paginator' => [new \stdClass(), false];

        yield 'Paginator' => [$this->getApiPlatformPaginator(), true];
    }

    /**
     * @param mixed $controllerResult
     *
     * @dataProvider providerTestListener
     */
    public function testListener($controllerResult, bool $isCustomPaginator): void
    {
        $event = new ViewEvent(
            $this->getKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $controllerResult
        );

        (new CustomPaginationListener())($event);

        self::assertEquals($isCustomPaginator, $event->getControllerResult() instanceof CustomPaginatorInterface);
    }

    /**
     * @return \ApiPlatform\Doctrine\Orm\Paginator<mixed>
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
            }
        );

        return new Paginator($doctrinePaginator);
    }
}
