<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Listeners;

use ApiPlatform\Doctrine\Orm\Paginator;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use EonX\EasyApiPlatform\Bridge\Symfony\Listeners\HttpKernelViewEventListener;
use EonX\EasyApiPlatform\Paginators\CustomPaginatorInterface;
use EonX\EasyApiPlatform\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class HttpKernelViewEventListenerTest extends AbstractSymfonyTestCase
{
    /**
     * @see testListener
     */
    public static function providerTestListener(): iterable
    {
        yield 'The controller result is not Paginator' => [
            'controllerResult' => new stdClass(),
            'isCustomPaginator' => false,
        ];

        yield 'The controller result is Paginator' => [
            'controllerResult' => self::getApiPlatformPaginator(),
            'isCustomPaginator' => true,
        ];
    }

    #[DataProvider('providerTestListener')]
    public function testListener(object $controllerResult, bool $isCustomPaginator): void
    {
        $event = new ViewEvent(
            $this->getKernel(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );
        $listener = new HttpKernelViewEventListener();

        $listener($event);

        self::assertSame($isCustomPaginator, $event->getControllerResult() instanceof CustomPaginatorInterface);
    }

    private static function getApiPlatformPaginator(): Paginator
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $manager */
        $manager = self::mock(EntityManagerInterface::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getConfiguration')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn(new Configuration());
        });

        $query = new Query($manager);
        $query->setFirstResult(1)
            ->setMaxResults(15);

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<object> $doctrinePaginator */
        $doctrinePaginator = self::mock(
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
