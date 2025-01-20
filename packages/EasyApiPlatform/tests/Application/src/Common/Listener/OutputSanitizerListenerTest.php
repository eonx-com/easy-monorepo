<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\src\Common\Listener;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyApiPlatform\Tests\Fixture\App\CustomPaginator\ApiResource\Category;

final class OutputSanitizerListenerTest extends AbstractApplicationTestCase
{
    public function testItSucceedsAndDoesNothingWhenDisabled(): void
    {
        $this->initDatabase();
        $entityManager = self::getService(EntityManagerInterface::class);
        $title = '<Some category>';
        $category = (new Category())->setTitle($title);
        $entityManager->persist($category);
        $entityManager->flush();

        $response = self::$client->request('GET', '/categories/' . $category->getId());

        $responseData = $response->toArray(false);

        self::assertSame($title, $responseData['title']);
    }

    public function testItSucceedsAndDoesNothingWhenEnabled(): void
    {
        self::setUpClient(['environment' => 'enable_output_sanitizer']);
        $this->initDatabase();
        $entityManager = self::getService(EntityManagerInterface::class);
        $category = (new Category())->setTitle('<Some category>');
        $entityManager->persist($category);
        $entityManager->flush();

        $response = self::$client->request('GET', '/categories/' . $category->getId());

        $responseData = $response->toArray(false);

        self::assertSame('&lt;Some category&gt;', $responseData['title']);
    }
}
