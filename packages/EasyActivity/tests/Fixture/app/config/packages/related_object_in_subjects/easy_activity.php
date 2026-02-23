<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;

/**
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsWithRelatedObjects
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [],
            Author::class => [],
        ],
    ],
]);
