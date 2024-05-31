<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Paginators;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyApiPlatform\Tests\AbstractApiTestCase;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\CustomPaginator\ApiResource\Category;

final class CustomPaginatorTest extends AbstractApiTestCase
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
}
