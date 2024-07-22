<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\ApiPlatform\Paginator;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyApiPlatform\Tests\Fixture\App\CustomPaginator\ApiResource\Category;

final class CustomPaginatorTest extends AbstractApplicationTestCase
{
    public function testCustomPaginator(): void
    {
        $this->initDatabase();
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist((new Category())->setTitle('Some category'));
        $entityManager->flush();

        $response = self::$client->request('GET', '/categories');

        $responseData = $response->toArray(false);
        self::assertSame(
            [
                'currentPage' => 1,
                'hasNextPage' => false,
                'hasPreviousPage' => false,
                'itemsPerPage' => 25,
                'totalItems' => 1,
                'totalPages' => 1,
            ],
            $responseData['pagination']
        );
    }

    public function testDefaultPaginator(): void
    {
        self::setUpClient(['environment' => 'default_paginator']);
        $this->initDatabase();
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist((new Category())->setTitle('Some category'));
        $entityManager->flush();

        $response = self::$client->request('GET', '/categories');

        $responseData = $response->toArray(false);
        self::assertFalse(isset($responseData['pagination']));
    }
}
