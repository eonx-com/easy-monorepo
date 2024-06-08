<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Article;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Author;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \EonX\EasyActivity\Tests\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsWithRelatedObjects
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Article::class)
        ->allowedProperties([
            'title',
            'author',
        ]);
    $easyActivityConfig->subjects(Author::class);
};
