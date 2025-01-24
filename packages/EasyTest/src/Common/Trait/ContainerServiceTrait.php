<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberInterface;
use Faker\Generator;

/**
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait ContainerServiceTrait
{
    protected static function enableActivityLogging(): void
    {
        self::getService(EasyDoctrineEntityEventsSubscriberInterface::class)->enable();
    }

    protected static function getEntityManager(): EntityManagerInterface
    {
        return self::getService(EntityManagerInterface::class);
    }

    protected static function getFaker(): Generator
    {
        /** @var \Faker\Generator $service */
        $service = self::getService('test.faker');

        return $service;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>|string $id
     *
     * @return ($id is class-string<TService> ? TService : object)
     */
    protected static function getService(string $id): object
    {
        /**
         * @var ($id is class-string<TService> ? TService : object) $service
         */
        $service = self::getContainer()->get($id);

        return $service;
    }
}
