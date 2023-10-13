<?php
declare(strict_types=1);

namespace EonX\EasyTest\Traits;

/**
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait ContainerServiceTrait
{
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
