<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;

/**
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsForSubjectsCreatedInTransaction
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsForUpdatedSubjects
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsWithCollections
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [
                'type' => 'article',
            ],
        ],
    ],
]);
