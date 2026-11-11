<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Doctrine\Logger;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Doctrine\DBAL\ParameterType;
use EonX\EasyBugsnag\Tests\Fixture\App\Entity\Author;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;

final class BreadcrumbLoggerTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        self::bootKernel(['environment' => 'doctrine_dbal']);
        $this->persistAuthor();

        $breadcrumb = $this->findInsertBreadcrumb();

        self::assertNotNull($breadcrumb);
        self::assertSame('SQL query | default', $breadcrumb->toArray()['name']);
        self::assertSame(
            'INSERT INTO author (id, name, position) VALUES (?, ?, ?)',
            $breadcrumb->getMetaData()['SQL']
        );
        self::assertSame(
            \enum_exists(ParameterType::class) ? '["STRING","STRING","INTEGER"]' : '[2,2,1]',
            $breadcrumb->getMetaData()['Types']
        );
        self::assertArrayNotHasKey('Values', $breadcrumb->getMetaData());
    }

    public function testItSucceedsWithQueryValues(): void
    {
        self::bootKernel(['environment' => 'doctrine_dbal_log_query_values']);
        $author = $this->persistAuthor();

        $breadcrumb = $this->findInsertBreadcrumb();

        self::assertNotNull($breadcrumb);
        self::assertSame(
            \json_encode([$author->getId(), 'Some Name', 1]),
            $breadcrumb->getMetaData()['Values']
        );
    }

    public function testItSucceedsWithoutBreadcrumbsByDefault(): void
    {
        $this->persistAuthor();

        $breadcrumb = $this->findInsertBreadcrumb();

        self::assertNull($breadcrumb);
    }

    private function findInsertBreadcrumb(): ?Breadcrumb
    {
        $client = self::getService(Client::class);
        /** @var object $recorder */
        $recorder = self::getPrivatePropertyValue($client, 'recorder');
        /** @var array<\Bugsnag\Breadcrumbs\Breadcrumb> $breadcrumbs */
        $breadcrumbs = self::getPrivatePropertyValue($recorder, 'breadcrumbs');

        foreach ($breadcrumbs as $breadcrumb) {
            if (\str_starts_with((string)($breadcrumb->getMetaData()['SQL'] ?? ''), 'INSERT INTO author')) {
                return $breadcrumb;
            }
        }

        return null;
    }

    private function persistAuthor(): Author
    {
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $author = new Author()
            ->setName('Some Name')
            ->setPosition(1);

        $entityManager->persist($author);
        $entityManager->flush();

        return $author;
    }
}
