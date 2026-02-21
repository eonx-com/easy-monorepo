<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Doctrine\Logger;

use Bugsnag\Client;
use EonX\EasyBugsnag\Tests\Fixture\App\Entity\Author;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;

final class BreadcrumbLoggerTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $author = new Author()
            ->setName('Some Name')
            ->setPosition(1);

        $entityManager->persist($author);
        $entityManager->flush();

        $client = self::getService(Client::class);
        /** @var object $recoder */
        $recoder = self::getPrivatePropertyValue($client, 'recorder');
        /** @var array $breadcrumbs */
        $breadcrumbs = self::getPrivatePropertyValue($recoder, 'breadcrumbs');
        /** @var \Bugsnag\Breadcrumbs\Breadcrumb $breadcrumb */
        $breadcrumb = $breadcrumbs[14];
        self::assertSame('SQL query | default', $breadcrumb->toArray()['name']);
        self::assertSame(
            'INSERT INTO author (id, name, position) VALUES (?, ?, ?)',
            $breadcrumb->getMetaData()['SQL']
        );
    }
}
